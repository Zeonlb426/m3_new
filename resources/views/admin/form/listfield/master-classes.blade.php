@php
    use App\Admin\Controllers\Api\DropDownListLoaderController;
    use App\Enums\LoaderType;
    use App\Enums\MorphMapperTarget;

    /** @var array $titles_content */
    /** @var array $themes */
    /** @var \App\Models\MasterClass\MasterClass<\App\Models\Competition\CompetitionMasterClass>[] $value */
@endphp
@php($listErrorKey = "masterClasses")
@php($sectionId = 'master_classes')

<div class="{{$viewClass['form-group']}} {{ $errors->has($listErrorKey) ? 'has-error' : '' }}">

    <div class="{{ $sectionId }} {{$viewClass['field']}}">

        @if($errors->has($listErrorKey))
            @foreach($errors->get($listErrorKey) as $message)
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label>
                <br/>
            @endforeach
        @endif

        <div class="row">
            <div class="col-sm-3 text-left">
                <input
                    type="checkbox"
                    class="master_classes_block_enabled"
                    value="1"
                    {{ ($titles_content['section_enabled'][MorphMapperTarget::MASTER_CLASS->value] ?? null) ? 'checked' : '' }}
                    name="titles_content[section_enabled][{{ MorphMapperTarget::MASTER_CLASS }}]"
                />
            </div>
            <div class="col-sm-3 text-right">
                <label class="control-label">
                    {{ \__('admin.models.competition.master_classes.pivot.titles_content.section_name') }}
                </label>
            </div>
            <div class="col-sm-6">
                <input
                    name="titles_content[section_name][{{ MorphMapperTarget::MASTER_CLASS }}]"
                    class="form-control"
                    value="{{ $titles_content['section_name'][MorphMapperTarget::MASTER_CLASS->value] ?? null }}"
                />
            </div>
            <div class="col-sm-12 text-right">
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle"></i>
                    &nbsp;
                    {!! \__('admin.models.competition.master_classes.pivot.titles_content.default') !!}
                </small>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
            <tr class="cell">
                <td colspan="3">
                    <div class="row">
                        <div class="col-sm-1">#</div>
                        <div class="col-sm-2">
                            {{ \__('admin.models.competition.master_classes.pivot.is_main') }}
                        </div>
                        <div class="col-sm-4">
                            {{ \__('admin.models.competition.master_classes.pivot.theme_ids') }}
                        </div>
                        <div class="col-sm-5">
                            {{ \__('admin.models.competition.master_classes.title') }}
                        </div>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody class="list-{{$column}}-table">

            @foreach($value ?: [] as $k => $v)
                <tr class="cell">
                    <td colspan="3">
                        <div class="row">
                            <div class="col-sm-1">
                                <a class="grid-sortable-handle" style="cursor: move;" data-sort="{pos}">
                                    <i class="fa fa-ellipsis-v"></i>
                                    <i class="fa fa-ellipsis-v"></i>
                                </a>
                                <input type="hidden" name="{{ $column }}[items][{{ $v->getKey() }}][order_column]"
                                       value="{{ $v->pivot->order_column }}">
                            </div>
                            @php($mainErrorKey = $column . '.' . 'main_id')
                            <div class="col-sm-2">
                                <label for="{{ $column }}[main_id]" class="control-label"></label>

                                <input
                                    class="iradio_flat-red"
                                    type="radio"
                                    data-id="{{ $v->getKey() }}"
                                    value="{{ $v->pivot->is_main ? $v->getKey() : '' }}"
                                    {{ $v->pivot->is_main ? 'checked' : '' }}
                                    name="{{ $column }}[main_id]"
                                />

                                @if(0 == $k)
                                    @include('admin::form.error', ['errorKey' => $mainErrorKey])
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <select name="{{ $column }}[items][{{ $v->getKey() }}][theme_ids][]" class="form-control"
                                        style="width: 100%" data-id="theme" multiple>
                                    @foreach($themes as $theme)
                                        @if(\in_array($theme['id'], $v->pivot->theme_ids))
                                            <option value="{{ $theme['id'] }}" selected>{{ $theme['text'] }}</option>
                                        @endif
                                    @endforeach
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="{{ $column }}[items][{{ $v->getKey() }}][id]" class="form-control"
                                        style="width: 100%" data-id="master-class">
                                    <option value="{{ $v->getKey() }}" selected>{{ $v->title }}</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                                    <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td></td>
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
            <td colspan="3">
                <div class="row">
                    <div class="col-sm-1">
                        <a class="grid-sortable-handle" style="cursor: move;" data-sort="{pos}">
                            <i class="fa fa-ellipsis-v"></i>
                            <i class="fa fa-ellipsis-v"></i>
                        </a>
                        <input type="hidden" name="{{ $column }}[items][{id}][order_column]" value="{pos}">
                    </div>
                    <div class="col-sm-2">
                        <label for="{{ $column }}[main_id]" class="control-label"></label>

                        <input class="iradio_flat-red" type="radio" data-id="{id}" name="{{ $column }}[main_id]">
                    </div>
                    <div class="col-sm-4">
                        <select name="{{ $column }}[items][{id}][theme_ids][]" class="form-control" style="width: 100%"
                                data-id="theme" multiple>
                            <option></option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select name="{{ $column }}[items][{id}][id]" class="form-control" required style="width: 100%"
                                data-id="master-class">
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                            <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </template>
</div>

<script>
    (function () {

        document.new_master_classes = 0;

        let themes = @json($themes);

        window.emitter.on('theme-remove', (removedId) => {
            themes = themes.filter((item) => item.id !== removedId);

            $(`select[data-id="theme"] option[value="${removedId}"]`, $('.{{ $sectionId }}')).remove();
        });
        window.emitter.on('theme-add', (addedItem) => {
            themes.push({id: addedItem.id, text: addedItem.text});

            const newOption = new Option(addedItem.text, addedItem.id, false, false);
            $('select[data-id="theme"]', $('.{{ $sectionId }}')).append(newOption).trigger('change');
        });

        let initMasterClasses = function () {
            $('input[type="radio"]', $(this)).each(function () {
                $(this).uncheckableRadio()
            });
            $('select[data-id="theme"]', $(this)).select2({
                data: themes,
                width: '100%',
                allowClear: true,
                placeholder: "{{ \__('admin.models.competition.master_classes.pivot.theme_ids') }}",
            });
            $('select[data-id="master-class"]', $(this)).select2({
                ajax: {
                    url: "{{ \route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::MASTER_CLASSES->value]) }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            exclude: $('.{{ $sectionId }} select[data-id="master-class"]').map(function () {
                                return $(this).select2('val')
                            }).toArray().join(',')
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.data, function (d) {
                                d.id = d.id;
                                d.text = d.text;
                                return d;
                            }),
                            pagination: {
                                more: data.next_page_url
                            }
                        };
                    },
                    cache: true
                },
                width: '100%',
                allowClear: false,
                placeholder: "{{ \__('admin.models.competition.master_classes.title') }}",
                minimumInputLength: 0,
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        };

        $('.master_classes_block_enabled').bootstrapSwitch({
            size: 'small',
            onText: '{{ \__('admin.switch_block_visible_statuses.on.text') }}',
            offText: '{{  \__('admin.switch_block_visible_statuses.off.text') }}',
            onColor: '{{  \__('admin.switch_block_visible_statuses.on.color') }}',
            offColor: '{{  \__('admin.switch_block_visible_statuses.off.color') }}'
        });

        $('.{{$column}}-add').off('click').on('click', function () {
            let tpl = $('template.{{$column}}-tpl').html();

            tpl = tpl
                .replaceAll('{id}', 'new_' + (document.new_master_classes++))
                .replaceAll('{pos}', $('tbody.list-{{$column}}-table').find('tr.cell').length + 1)
            ;
            $('tbody.list-{{$column}}-table').append(tpl);
            initMasterClasses.call(
                $('.{{ $sectionId }} tbody tr:last-child')
            );
            window.refreshOrdering();
        });
        $('.{{ $sectionId }} .{{$column}}-remove').off('click').on('click', function () {
            $(this).closest('tr').empty().remove();
            window.refreshOrdering();
        });

        $(".{{ $sectionId }} * tbody").sortable({
            placeholder: "sort-highlight",
            handle: ".grid-sortable-handle",
            forcePlaceholderSize: true,
            zIndex: 999999
        }).on("sortupdate", function (event, ui) {
            window.refreshOrdering()
        });

        $(document).on('change', '.{{ $sectionId }} input[type="radio"]', function () {
            $('.{{ $sectionId }} input[type="radio"]:not([value=""])').val(null);
            if ($(this).is(':checked')) {
                $(this).val($(this).data('id'));
            }
        });

        $(document).find('.{{ $sectionId }} .{{ $column }}-remove').each(function () {
            initMasterClasses.call($(this).parents('tr'));
        });
    })();
</script>
