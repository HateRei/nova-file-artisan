<?php

namespace Mostafaznv\NovaFileArtisan\Fields;

use Laravel\Nova\Fields\Text;
use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;


class NovaFileArtisanMeta
{
    protected string $attachment;

    public function __construct(string $attachment)
    {
        $this->attachment = $attachment;
    }

    public static function make(string $attachment): self
    {
        return new static($attachment);
    }


    public function all($labels = []): array
    {
        return [
            $this->fileName($labels['name'] ?? 'File Name'),
            $this->size($labels['size'] ?? 'Size'),
            $this->mimeType($labels['mime_type'] ?? 'MIME Type'),
            $this->format($labels['width'] ?? 'Width'),
            $this->width($labels['height'] ?? 'Height'),
            $this->height($labels['duration'] ?? 'Duration'),
            $this->duration($labels['format'] ?? 'Format'),
        ];
    }

    public function fileName($label = 'File Name'): Text
    {
        return $this->meta('name', $label);
    }

    public function size($label = 'Size'): Text
    {
        return $this->meta('size', $label);
    }

    public function mimeType($label = 'MIME Type'): Text
    {
        return $this->meta('mime_type', $label);
    }

    public function width($label = 'Width'): Text
    {
        return $this->meta('width', $label);
    }

    public function height($label = 'Height'): Text
    {
        return $this->meta('height', $label);
    }

    public function duration($label = 'Duration'): Text
    {
        return $this->meta('duration', $label);
    }

    public function format($label = 'Format'): Text
    {
        return $this->meta('format', $label);
    }

    protected function meta(string $name, string $label): Text
    {
        $attachment = $this->attachment;

        return Text::make(trans($label), "{$attachment}_file_$name")
            ->readonly()
            ->exceptOnForms()
            ->displayUsing(function($value, $model) use ($attachment, $name) {
                if ($attachment and is_a($model->{$attachment}, AttachmentProxy::class)) {
                    $output = $model->attachment($attachment)->meta($name);

                    if ($name === 'duration') {
                        $output = $this->humanReadableDuration($output);
                    }
                    else if ($name === 'size') {
                        $output = $this->humanReadableBytes($output);
                    }
                }
                else {
                    $output = '–';
                }

                return $output;
            });
    }

    protected function humanReadableDuration(?int $seconds): string
    {
        if (is_null($seconds)) {
            return '—';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60 % 60);
        $seconds = floor($seconds % 60);
        $output = [];

        if ($hours) {
            $output[] = $hours . 'h';
        }

        if ($minutes) {
            $output[] = $minutes . 'm';
        }

        if ($seconds) {
            $output[] = $seconds . 's';
        }

        return implode(' ', $output);
    }

    protected function humanReadableBytes(?int $bytes): string
    {
        if (is_null($bytes)) {
            return '—';
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
