@php use App\Admin\Controllers\Api\DropDownListLoaderController;use App\Enums\LoaderType;use Nicklasos\LaravelAdmin\MediaLibrary\MediaLibraryFile; @endphp
@php($listErrorKey = "$column.values")
@php($sectionId = 'prize')

<div class="{{$viewClass['form-group']}} {{ $errors->has($listErrorKey) ? 'has-error' : '' }}">

    <div class="{{ $sectionId }} {{$viewClass['field']}}">

        @if($errors->has($listErrorKey))
            @foreach($errors->get($listErrorKey) as $message)
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label>
                <br/>
            @endforeach
        @endif

        <div class="row">
            <div class="col-md-12">
                <div style="height: 20px; border-bottom: 1px solid #eee; text-align: center;margin-bottom: 10px;">
                    <span style="font-size: 18px; background-color: #ffffff; padding: 0 10px;">
                    {{ \__('admin.models.competition.prizes_info.title') }}
                    </span>
                </div>
            </div>

            <div class="col-sm-12 text-right">
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle"></i>
                    &nbsp;
                    {!! \__('admin.models.competition.prizes_info.default') !!}
                </small>
            </div>
        </div>

        <?php
        /* @var $prizeInfo \App\Models\Competition\PrizeInfo */
        $prizeInfoAttributes = $prizeInfo?->titles_content ?: [];
        ?>
        <div class="box-body">
            <div class="fields-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title" class="col-sm-4 control-label">
                            {{ \__('admin.models.competition.prizes_info.pivot.titles_content.like') }}
                        </label>

                        <div class="col-sm-7">
                            <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                                <input
                                    name="prizeInfo[titles_content][like_text]"
                                    class="form-control"
                                    value="{{ $prizeInfoAttributes['like_text'] ?? null }}"
                                    placeholder="{{ \__('admin.models.competition.prizes_info.pivot.titles_content.like_default') }}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fields-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title" class="col-sm-4 control-label">
                            {{ \__('admin.models.competition.prizes_info.pivot.titles_content.gift') }}
                        </label>

                        <div class="col-sm-7">
                            <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                                <input
                                    name="prizeInfo[titles_content][gift_text]"
                                    class="form-control"
                                    value="{{ $prizeInfoAttributes['gift_text'] ?? null }}"
                                    placeholder="{{ \__('admin.models.competition.prizes_info.pivot.titles_content.gift_default') }}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
            <tr>
                <td colspan="2">
                    <div class="row">
                        <div class="col-sm-1">#</div>
                        <div class="col-sm-11">
                            {{ \__('admin.models.competition.prizes.title') }}
                        </div>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody class="list-{{$column}}-table">

            @foreach(old("{$column}.values", ($value ?: [])) as $k => $v)

                <?php /* @var $v \App\Models\Competition\Prize */?>

                <tr class="cell">
                    <td>
                        <div class="row">
                            <div class="col-sm-4">
                                <label>
                                    {{ \__('admin.models.prizes.title') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                                    <input
                                        name="{{ $column }}[{{ $v->getKey() }}][title]"
                                        class="form-control"
                                        value="{{ $v->title }}"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label>
                                    {{ \__('admin.models.prizes.link') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-internet-explorer fa-fw"></i></span>

                                    <input
                                        type="url"
                                        name="{{ $column }}[{{ $v->getKey() }}][link]"
                                        class="form-control"
                                        value="{{ $v->link }}"
                                    />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label>
                                    {{ \__('admin.models.prizes.win_position') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-bar-chart fa-fw"></i></span>
                                    <input
                                        style="text-align: center;"
                                        type="number"
                                        name="{{ $column }}[{{ $v->getKey() }}][win_position]"
                                        class="form-control"
                                        value="{{ $v->win_position }}"
                                    />
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <br>
                                <label>
                                    {{ \__('admin.models.prizes.description') }}
                                </label>
                                <textarea
                                    name="{{ $column }}[{{ $v->getKey() }}][description]"
                                    class="ckeditor"
                                    cols="30" rows="10" required
                                >{{ $v->description }}</textarea>
                            </div>
                            <div class="col-sm-12">
                                <br>
                                <label>
                                    {{ \__('admin.models.prizes.image') }}
                                </label>
                                <input
                                    type="file"
                                    name="{{ $column }}[{{ $v->getKey() }}][image]"
                                    data-initial-preview="{{ $v->image }}"
                                    data-initial-caption="{{ $v->image ? \Arr::last(\explode('/', $v->image)) : null }}"
                                />
                            </div>
                        </div>
                    </td>

                    <td style="width: 75px;">
                        <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                            <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <div class="{{ $column }}-add btn btn-success btn-sm pull-right">
                        <i class="fa fa-save"></i>&nbsp;{{ __('admin.new') }}
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<div>
    <template class="{{$column}}-tpl">
        <tr class="cell">
            <td>
                <div class="row">
                    <div class="col-sm-4">
                        <label>
                            {{ \__('admin.models.prizes.title') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                            <input name="{{ $column }}[{id}][title]" class="form-control" required/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label>
                            {{ \__('admin.models.prizes.link') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-internet-explorer fa-fw"></i></span>

                            <input type="url" name="{{ $column }}[{id}][link]" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label>
                            {{ \__('admin.models.prizes.win_position') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-bar-chart fa-fw"></i></span>
                            <input
                                style="text-align: center;"
                                type="number"
                                name="{{ $column }}[{id}][win_position]"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <br>
                        <label>
                            {{ \__('admin.models.prizes.description') }}
                        </label>
                        <textarea name="{{ $column }}[{id}][description]" class="ckeditor" cols="30" rows="10" required></textarea>
                    </div>
                    <div class="col-sm-12">
                        <br>
                        <label>
                            {{ \__('admin.models.prizes.image') }}
                        </label>
                        <input type="file" name="{{ $column }}[{id}][image]" />
                    </div>
                </div>
            </td>

            <td style="width: 75px;">
                <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                    <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                </div>
            </td>
        </tr>
    </template>
</div>

<script>
    (function () {

        document.new_prizes = 0;

        let initPrizes = function () {

            CKEDITOR.replaceAll('ckeditor', {lang: 'ru-RU', height: 300});
            $('textarea.ckeditor').each(function() {
                $(this).removeClass('ckeditor')
            });

            $('input[type="file"]', $(this)).fileinput({
                overwriteInitial:true,
                initialPreviewAsData:true,
                msgPlaceholder:"{{ \__('admin.choose_file') }}",
                browseLabel:"{{ \__('admin.browse') }}",
                cancelLabel:"{{ \__('admin.cancel') }}",
                showRemove:false,
                showUpload:false,
                showCancel:false,
                dropZoneEnabled:false,
                fileActionSettings:{
                    showRemove:false,
                    showDrag:false
                },
                allowedFileTypes:["image"]
            });
        };

        $('.{{$column}}-add').off('click').on('click', function () {
            let tpl = $('template.{{$column}}-tpl').html();

            tpl = tpl
                .replaceAll('{id}', 'new_' + (document.new_prizes++))
                .replaceAll('{pos}', $('tbody.list-{{$column}}-table').find('tr.cell').length + 1)
            ;
            $('tbody.list-{{$column}}-table').append(tpl);
            initPrizes.call(
                $('.{{ $sectionId }} tbody tr:last-child')
            );
        });
        $('.{{$column}}-remove').off('click').on('click', function () {
            $(this).closest('tr').empty().remove();
        });

        $(document).find('.{{ $sectionId }} .{{ $column }}-remove').each(function () {
            initPrizes.call($(this).parents('tr'));
        });
    })();
</script>
