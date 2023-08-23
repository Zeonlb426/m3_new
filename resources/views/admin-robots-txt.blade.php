<?php

/**
 * @var string $fromAction
 * @var array $data
 */
?>

<form action="{{ $formAction }}" method="post">
    @csrf
    <textarea autofocus rows="10" cols="50" style="resize:none" name="contentRobots">{{ $data['contentRobots'] }}</textarea>
    <div>
        <input class="btn btn-primary" type="submit" value="Сохранить">
    </div>
</form>
