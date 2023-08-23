<?php

namespace Nicklasos\LaravelAdmin\MediaLibrary;

use Encore\Admin\Form\Field\MultipleFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaLibraryMultipleFile extends MultipleFile
{
    use MediaLibraryBase;

    protected $view = 'admin::form.multiplefile';

    /**
     * @param array $data
     */
    public function fill($data)
    {
        parent::fill($data);

        $value = $this->form->model()->getMedia($this->column());

        foreach ($value as $key => $media) {
            $this->value[$key] = $media->id;
        }
    }

    /**
     * @param array $data
     */
    public function setOriginal($data)
    {
        $value = $this->form->model()->getMedia($this->column());

        foreach ($value as $key => $media) {
            $this->original[$key] = $media->id;
        }
    }

    /**
     * @param UploadedFile|null $file
     *
     * @return mixed|string
     */
    protected function prepareForeach(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        return $this->uploadMedia($file);
    }

    /**
     * @return array
     */
    protected function initialPreviewConfig(): array
    {
        $medias = Media::whereIn('id', $this->value ?: [])->orderBy('order_column')->get();

        $config = [];
        foreach ($medias as $media) {
            $config[] = $this->getPreviewEntry($media);
        }

        return $config;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function destroy($key): array
    {
        $files = $this->original ?: [];

        foreach ($files as $fileKey => $file) {
            if ($file == $key) {
                $media = Media::whereId($key)->first();
                $media->delete();
            }
        }

        return array_values($files);
    }

    /**
     * @param string $order
     *
     * @return array
     */
    protected function sortFiles($order): array
    {
        $start = \min($this->form->model()->getMedia($this->id)->pluck('order_column')->toArray());
        $imageArr = \explode(',', $order);

        Media::setNewOrder($imageArr, $start);

        return parent::sortFiles($order);
    }
}
