<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring leads from the old DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';
        $arrayExt = ['jpg', 'jpeg', 'png'];
        $sortForEnable = 1;
        $sortForDisable = 100;

        $this->newLine();
        $this->info('=== Начат процесс переноса Ведущих ===');

        Lead::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_lead')
//            ->whereRaw('
//                enabled = true
//                OR EXISTS(SELECT id FROM dvlp_work_category_lead WHERE dvlp_work_category_lead.lead_id = dvlp_lead.id)
//                OR EXISTS(SELECT id FROM dvlp_competition_lead WHERE dvlp_competition_lead.lead_id = dvlp_lead.id)
//                OR (SELECT COUNT(NAME) FROM dvlp_lead AS t WHERE t.name = dvlp_lead.name ) = 1
//            ')
            ->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_lead')
            ->select(
                'id',
                'sort',
                'name',
                'slug',
                'image',
                'content',
                'enabled',
                'created_at',
                'updated_at',
                'short_description'
            )
//            ->whereRaw('
//                enabled = true
//                OR EXISTS(SELECT id FROM dvlp_work_category_lead WHERE dvlp_work_category_lead.lead_id = dvlp_lead.id)
//                OR EXISTS(SELECT id FROM dvlp_competition_lead WHERE dvlp_competition_lead.lead_id = dvlp_lead.id)
//                OR (SELECT COUNT(NAME) FROM dvlp_lead AS t WHERE t.name = dvlp_lead.name ) = 1
//            ')
            ->orderBy('id')
            ->chunk(5, function ($leads) use ($progressBar, $sortForEnable, $sortForDisable, $baseURL, $tempLocation, $arrayExt) {
                foreach($leads as $lead) {
                    $data = [
                        'id' => $lead->id,
                        'created_at' => $lead->created_at,
                        'updated_at' => $lead->updated_at,
                        'name' => $lead->name,
                        'slug' => $lead->slug,
                        'visible_status' => $lead->enabled ? 1 : 0,
                        'short_description' => empty($lead->short_description) ? null : $lead->short_description,
                        'description' => empty($lead->content) ? "<p>Деятель искусства</p>" : $lead->content,
                        'order_column' => $lead->enabled ? $sortForEnable : $sortForDisable,
                    ];

                    if ($lead->enabled) {
                        $sortForEnable = $sortForEnable + 1;
                    }else{
                        $sortForDisable = $sortForDisable + 1;
                    }

                    \DB::table('leads')->insert($data);

                    if(empty($lead->image) || mb_substr($lead->image, 0, 1) !== '/') continue;

                    $url = $baseURL . $lead->image;

                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $arrayExt)) continue;

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $image = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) continue;

                    $newLead = Lead::where('id', $lead->id)->first();

                    $tempFile = $tempLocation . 'lead_image_' . $lead->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $newLead->addMedia($tempFile)->toMediaCollection(Lead::PHOTO_COLLECTION);
                }

                $progressBar->advance(5);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('leads')->max('id');
        $rawSelect = sprintf("SELECT setval('leads_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Ведущих закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
