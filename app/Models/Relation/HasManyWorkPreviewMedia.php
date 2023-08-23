<?php

declare(strict_types=1);

namespace App\Models\Relation;

use App\Models\Competition\Competition;
use App\Models\Competition\Theme;
use App\Models\CompetitionWork\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

final class HasManyWorkPreviewMedia extends HasMany
{
    public function __construct(Work $parent)
    {
        $mediaClass = \config('media-library.media_model');

        /** @var \App\Models\Media\Media $mediaInstance */
        $mediaInstance = new $mediaClass();

        parent::__construct($mediaInstance->newQuery(), $parent, 'model_id', 'id');
    }

    /**
     * {@inheritDoc}
     *
     * @param \App\Models\CompetitionWork\Work[] $models
     */
    public function addEagerConstraints(array $models): void
    {
        $competitionIds = \collect($models)->pluck('competition_id')->filter()->unique()->values()->all();
        $themeIds = \collect($models)->pluck('theme_id')->filter()->unique()->values()->all();

        $this->getRelationQuery()
            ->where(fn(Builder $query): Builder => $this->addThemeMediaConstraints($query, $themeIds))
            ->orWhere(fn(Builder $query): Builder => $this->addCompetitionMediaConstraints($query, $competitionIds))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function addConstraints(): void
    {
        if (false === self::$constraints) {
            return;
        }

        $this->getRelationQuery()
            ->where(fn(Builder $query): Builder => $this->addThemeMediaConstraints(
                $query, [$this->parent->getAttribute('theme_id')],
            ))
            ->orWhere(fn(Builder $query): Builder => $this->addCompetitionMediaConstraints(
                $query, [$this->parent->getAttribute('competition_id')],
            ))
        ;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media $query
     * @param array<int|null> $ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function addThemeMediaConstraints(Builder $query, array $ids): Builder
    {
        $query = $query
            ->where(['model_type' => (new Theme)->getMorphClass()])
        ;

        if (1 === \count($ids)) {
            $query = $query->where('model_id', '=', $ids[0]);
        } else {
            $query = $query->whereIn('model_id', $ids);
        }

        return $query
            ->whereIn('collection_name', [
                Theme::TILE_COLLECTION,
                Theme::COVER_COLLECTION,
            ])
        ;
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media $query
     * @param array<int|null> $ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function addCompetitionMediaConstraints(Builder $query, array $ids): Builder
    {
        $query = $query
            ->where(['model_type' => (new Competition)->getMorphClass()])
        ;

        if (1 === \count($ids)) {
            $query = $query->where('model_id', '=', $ids[0]);
        } else {
            $query = $query->whereIn('model_id', $ids);
        }

        return $query
            ->whereIn('collection_name', [
                Competition::TILE_COLLECTION,
                Competition::COVER_COLLECTION,
            ])
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @param \App\Models\CompetitionWork\Work[] $models
     */
    protected function matchOneOrMany(array $models, EloquentCollection $results, $relation, $type): array
    {
        $dictionary = $this->buildDictionary($results);
        $competitionMorph = (new Competition)->getMorphClass();
        $themeMorph = (new Theme)->getMorphClass();

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $medias = new MediaCollection();

            if (null !== $model->competition_id && isset($dictionary[$competitionMorph][$model->competition_id])) {
                $medias = $medias->merge($dictionary[$competitionMorph][$model->competition_id]);
            }

            if (null !== $model->theme_id && isset($dictionary[$themeMorph][$model->theme_id])) {
                $medias = $medias->merge($dictionary[$themeMorph][$model->theme_id]);
            }

            $model->setRelation($relation, $medias);
        }

        return $models;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Illuminate\Database\Eloquent\Collection<\App\Models\Media\Media> $results
     *
     * @return array<string, array<int, \App\Models\Media\Media[]>>
     */
    protected function buildDictionary(EloquentCollection $results): array
    {
        return $results
            ->groupBy('model_type')
            ->map(function (Collection $items): array {
                return $items
                    ->groupBy('model_id')
                    ->map(fn(Collection $modelItems): array => $modelItems->values()->all())
                    ->all()
                ;
            })
            ->all()
        ;
    }
}
