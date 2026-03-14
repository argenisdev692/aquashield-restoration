<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\Http\Export;

use Modules\Blog\Infrastructure\Persistence\Eloquent\Models\PostEloquentModel;

final class PostExportTransformer
{
    #[\NoDiscard]
    public static function transform(PostEloquentModel $post): array
    {
        return $post
            |> self::toExcelData(...)
            |> self::formatDates(...)
            |> self::sanitizeValues(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(PostEloquentModel $post): array
    {
        return $post
            |> self::toPdfData(...)
            |> self::formatDates(...)
            |> self::sanitizeValues(...);
    }

    private static function toExcelData(PostEloquentModel $post): array
    {
        return [
            'uuid' => $post->uuid,
            'title' => $post->post_title,
            'slug' => $post->post_title_slug,
            'category' => $post->category?->blog_category_name,
            'publication_status' => $post->post_status,
            'status' => $post->deleted_at !== null ? 'Inactive' : 'Active',
            'published_at' => $post->published_at?->toIso8601String(),
            'created_at' => $post->created_at?->toIso8601String(),
        ];
    }

    private static function toPdfData(PostEloquentModel $post): array
    {
        return [
            'uuid' => $post->uuid,
            'title' => $post->post_title,
            'slug' => $post->post_title_slug,
            'category' => $post->category?->blog_category_name,
            'publication_status' => $post->post_status,
            'status' => $post->deleted_at !== null ? 'Inactive' : 'Active',
            'published_at' => $post->published_at?->toIso8601String(),
            'created_at' => $post->created_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $data): array
    {
        foreach (['published_at', 'created_at'] as $field) {
            $value = $data[$field] ?? null;

            if (!is_string($value) || $value === '') {
                continue;
            }

            try {
                $data[$field] = (new \DateTimeImmutable($value))->format('F j, Y');
            } catch (\Exception) {
            }
        }

        return $data;
    }

    private static function sanitizeValues(array $data): array
    {
        return array_map(
            static fn(mixed $value): string => match (true) {
                $value === null => '—',
                is_string($value) && $value === '' => '—',
                default => (string) $value,
            },
            $data,
        );
    }
}
