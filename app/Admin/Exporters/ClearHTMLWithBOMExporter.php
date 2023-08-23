<?php

declare(strict_types = 1);

namespace App\Admin\Exporters;

use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Exporters\CsvExporter;
use JetBrains\PhpStorm\NoReturn;

final class ClearHTMLWithBOMExporter extends CsvExporter
{
    /**
     * @param string $column
     * @param mixed  $value
     * @param mixed  $original
     *
     * @return mixed
     */
    protected function getColumnValue(string $column, $value, $original): mixed
    {
        if (\is_bool($original)) {
            $original = $original ? \__('admin.yes') : \__('admin.no');
        }

        if (\is_string($value) && \preg_match('/(?<=<)\/?[a-z]+\s*(?=[^<]*?>)/i', $value)) {
            if (isset($this->columnCallbacks[$column])) {
                return $this->columnCallbacks[$column]($original, $original);
            }

            if (\Str::contains($value, 'img')) {
                \preg_match('/<img.*src\s*=\s*"(.*)"/iU', $value, $result);
                return array_pop($result);
            }
            if (\Str::contains($value, '<a') && \Str::contains($value, 'href')) {
                \preg_match('/>(.*)</U', $value, $result);
                return array_pop($result);
            }

            return $original;
        }

        if (!empty($this->columnUseOriginalValue)
            && in_array($column, $this->columnUseOriginalValue)) {
            return $original;
        }

        if (isset($this->columnCallbacks[$column])) {
            return $this->columnCallbacks[$column]($value, $original);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    #[NoReturn]
    public function export(): void
    {
        if ($this->callback) {
            \call_user_func($this->callback, $this);
        }

        $response = function () {
            $handle = \fopen('php://output', 'w');
            //BOM
            \fprintf($handle, chr(239) . chr(187) . chr(191));
            $titles = [];

            $this->chunk(function ($collection) use ($handle, &$titles) {
                Column::setOriginalGridModels($collection);

                $original = $current = $collection->toArray();

                $this->grid->getColumns()->map(function (Column $column) use (&$current) {
                    $current = $column->fill($current);
                    $this->grid->columnNames[] = $column->getName();
                });

                // Write title
                if (empty($titles)) {
                    \fputcsv($handle, $titles = $this->getVisiableTitles(), ';');
                }

                // Write rows
                foreach ($current as $index => $record) {
                    \fputcsv($handle, $this->getVisiableFields($record, $original[$index]), ';');
                }
            });
            \fclose($handle);
        };

        \response()->stream($response, 200, $this->getHeaders())->send();

        exit;
    }
}
