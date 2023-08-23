<?php

declare(strict_types=1);

namespace App\Admin\Exporters;

use App\Admin\Exceptions\TooMuchWorksForExportException;
use App\Enums\Competition\WorkTypeSlug;
use App\Enums\CompetitionWork\ApproveStatus;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use JetBrains\PhpStorm\NoReturn;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class WorkExporter
 * @package App\Admin\Exporters
 *
 * @method \Illuminate\Database\Schema\Builder|\App\Models\CompetitionWork\Work getQuery()
 */
final class WorkExporter extends ExcelExporter implements ShouldAutoSize, WithColumnWidths, WithHeadings, WithStyles, WithMapping
{
    use Exportable;

    protected $fileName = 'Works.xlsx';

    /**
     * @return void
     */
    #[NoReturn]
    public function export(): void
    {
        #todo: make export queued
        \ini_set('memory_limit', '2048M');
        \set_time_limit(0);

        $totalCount = $this->getQuery()->count();

        if ($totalCount > 10_000) {
            throw new TooMuchWorksForExportException();
        }

        $this->download($this->fileName)->prepare(request())->send();

        exit;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 10,
            'C' => 34,
            'D' => 34,
            'E' => 25,
            'F' => 20,
            'G' => 18,
            'H' => 33,
            'I' => 14,
            'J' => 30,
            'K' => 30,
            'L' => 10,
            'M' => 14,
            'N' => 100,
            'O' => 23,
        ];
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'ID работы',
            'Статус',
            'Имя пользователя',
            'Имя автора',
            'Регион',
            'Город',
            'Возраст автора',
            'Email',
            'Телефон',
            'Соц.сеть',
            'Конкурс',
            'Лайки',
            'Тип работы',
            'Работа пользователя',
            'Дата создания',
        ];
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return array[]
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @param \App\Models\CompetitionWork\Work $workModel
     *
     * @return array
     */
    public function map($workModel): array
    {
        $work = $workModel->toArray();

        $timeBirthDate = $work['author']['birth_date'] ? strtotime($work['author']['birth_date']) : 0;
        $timeCreatedWork = $work['created_at'] ? strtotime($work['created_at']) : 0;

        $years = '-';
        if ($timeBirthDate && $timeCreatedWork) {
            $diff = \abs($timeCreatedWork - $timeBirthDate);
            $years = \intdiv($diff, (365 * 60 * 60 * 24));
        }

        $socialParts = [];
        if (false === empty($work['user']['user_social'])) {
            foreach ($work['user']['user_social'] as $soc) {
                $socialParts[] = match ($soc['provider']) {
                    "vkontakte" => "https://vk.com/id{$soc['external_user_id']}",
                    "facebook" => "https://fb.com/{$soc['external_user_id']}",
                    "odnoklasniki" => "https://ok.ru/profile/{$soc['external_user_id']}",
                    default => "не известная соцсеть",
                };
            }
        }

        $social = \implode("\n", $socialParts);

        $contentParts = [];
        if (false === empty($work['content'][WorkTypeSlug::AUDIO->value])) {
            $contentParts[] = '=HYPERLINK("' . $work['content'][WorkTypeSlug::AUDIO->value] . '")';
        }
        if (false === empty($work['content'][WorkTypeSlug::IMAGES->value])) {
            foreach ($work['content'][WorkTypeSlug::IMAGES->value] as $img) {
                $contentParts[] = '=HYPERLINK("' . $img . '")';
            }
        }
        if (false === empty($work['content'][WorkTypeSlug::IMAGE->value])) {
            $contentParts[] = '=HYPERLINK("' . $work['content'][WorkTypeSlug::IMAGE->value] . '")';
        }
        if (false === empty($work['work_video'])) {
            $contentParts[] = '=HYPERLINK("' . $work['work_video'] . '")';
        }
        if (false === empty($work['work_text'])) {
            $contentParts[] = $work['work_text'];
        }
        $content = \implode("\n", $contentParts);
        return [
            $work['id'],
            ApproveStatus::from($work['status'])->label(),
            $work['user']['first_name'] . ' ' . $work['user']['last_name'],
            $work['author']['name'] ?? '',
            $work['user']['region']['title'] ?? '',
            $work['user']['city']['title'] ?? '',
            $years,
            $work['user']['email'],
            $work['user']['phone'],
            $social,
            $work['competition']['title'],
            $work['likes_total_count'],
            $work['work_type']['title'],
            $content,
            $work['created_at'],
        ];
    }

    public function query()
    {
        return $this
            ->getQuery()
            ->with([
                'author:id,name,birth_date',
                'user:id,first_name,last_name,email,phone,city_id,region_id',
                'user.region:id,title',
                'user.city:id,title',
                'user.userSocial:id,user_id,provider,external_user_id',
                'competition:id,title',
                'workType',
                'media',
            ])
            ->select([
                'id',
                'competition_id',
                'theme_id',
                'user_id',
                'author_id',
                'work_type_id',
                'status',
                'likes_total_count',
                'work_video',
                'work_text',
                'created_at',
            ])
        ;
    }
}
