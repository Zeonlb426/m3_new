@php
    use App\Admin\Controllers\Api\DropDownListLoaderController;
    use App\Enums\LoaderType;
    use App\Enums\MorphMapperTarget;
@endphp

@php($listErrorKey = "themes")
@php($sectionId = 'themes')

<div class="{{$viewClass['form-group']}} {{ $errors->has($listErrorKey) ? 'has-error' : '' }}">

    <div class="{{ $sectionId }} {{$viewClass['field']}}">

        @if($errors->has($listErrorKey))
            @foreach($errors->get($listErrorKey) as $message)
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label>
                <br/>
            @endforeach
        @endif

        <div class="row">
            <div class="col-sm-12">
                <input
                    type="checkbox"
                    class="themes_enabled"
                    value="1"
                    {{ ($titles_content['themes_enabled'] ?? null) ? 'checked' : '' }}
                    name="titles_content[themes_enabled]"
                />
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-sm-3 text-left">
                <input
                    type="checkbox"
                    class="themes_block_enabled"
                    value="1"
                    {{ ($titles_content['section_enabled'][MorphMapperTarget::THEME->value] ?? null) ? 'checked' : '' }}
                    name="titles_content[section_enabled][{{ MorphMapperTarget::THEME }}]"
                />
            </div>
            <div class="col-sm-3 text-right">
                <label class="control-label">
                    {{ \__('admin.models.competition.themes.pivot.titles_content.section_name') }}
                </label>
            </div>
            <div class="col-sm-6">
                <input
                    name="titles_content[section_name][{{ MorphMapperTarget::THEME }}]"
                    class="form-control"
                    value="{{ $titles_content['section_name'][MorphMapperTarget::THEME->value] ?? null }}"
                />
            </div>

            <div class="col-sm-12 text-right">
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle"></i>
                    &nbsp;
                    {!! \__('admin.models.competition.themes.pivot.titles_content.default') !!}
                </small>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
            <tr>
                <td colspan="3">
                    <div class="row">
                        <div class="col-sm-2">#</div>
                        <div class="col-sm-10">
                            {{ \__('admin.models.competition.themes.title') }}
                        </div>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody class="list-{{$column}}-table">

            @foreach($value ?: [] as $k => $v)
                    <?php /* @var $v \App\Models\Competition\Theme */ ?>

                <tr class="cell">
                    <td style="width: 30px;">
                        <a class="grid-sortable-handle" style="cursor: move;" data-sort="{pos}">
                            <i class="fa fa-ellipsis-v"></i>
                            <i class="fa fa-ellipsis-v"></i>
                        </a>
                        <input type="hidden" name="{{ $column }}[{{ $v->getKey() }}][order_column]"
                               value="{{ $v->_pivot_order_column }}">
                    </td>
                    <td>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <select name="{{ $column }}[{{ $v->getKey() }}][id]" class="form-control"
                                        style="width: 100%">
                                    <option value="{{ $v->getKey() }}" selected>{{ $v->title }}</option>
                                </select>
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
            <td style="width: 30px;">
                <a class="grid-sortable-handle" style="cursor: move;" data-sort="{pos}">
                    <i class="fa fa-ellipsis-v"></i>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <input type="hidden" name="{{ $column }}[{id}][order_column]">
            </td>
            <td>
                <div class="form-group">
                    <div class="col-sm-12">
                        <select name="{{ $column }}[{id}][id]" class="form-control" style="width: 100%">
                        </select>
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

        document.new_themes = 0;

        let initThemes = function () {
            $('select', $(this)).select2({
                ajax: {
                    url: "{{ \route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::THEMES->value]) }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            exclude: $('.{{ $sectionId }} select').map(function () {
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
                placeholder: "{{ \__('admin.models.competition.themes.title') }}",
                minimumInputLength: 0,
                escapeMarkup: function (markup) {
                    return markup;
                }
            }).on('select2:selecting', (event) => {
                const deletedId = $(event.target).val();

                window.emitter.emit('theme-remove', deletedId);
            }).on('select2:select', (event) => {
                const addedItem = event.params.data;

                window.emitter.emit('theme-add', addedItem);
            });

            $('.{{$column}}-remove').off('click').on('click', function () {
                const row = $(this).closest('tr');
                const removedId = $('select', row).val();

                if (removedId) {
                    window.emitter.emit('theme-remove', removedId);
                }

                row.empty().remove();
                window.refreshOrdering();
            });
        };

        $('.themes_enabled').bootstrapSwitch({
            size: 'small',
            onText: '{{ \__('admin.switch_visible_statuses.on.text') }}',
            offText: '{{  \__('admin.switch_visible_statuses.off.text') }}',
            onColor: '{{  \__('admin.switch_block_visible_statuses.on.color') }}',
            offColor: '{{  \__('admin.switch_block_visible_statuses.off.color') }}',
            labelText: '{{ \__('admin.models.competition.themes.title') }}'
        });

        $('.themes_block_enabled').bootstrapSwitch({
            size: 'small',
            onText: '{{ \__('admin.switch_block_visible_statuses.on.text') }}',
            offText: '{{  \__('admin.switch_block_visible_statuses.off.text') }}',
            onColor: '{{  \__('admin.switch_block_visible_statuses.on.color') }}',
            offColor: '{{  \__('admin.switch_block_visible_statuses.off.color') }}'
        });

        $('.{{$column}}-add').off('click').on('click', function () {
            let tpl = $('template.{{$column}}-tpl').html();

            tpl = tpl
                .replaceAll('{id}', 'new_' + (document.new_themes++))
                .replaceAll('{pos}', $('.list-{{$column}}-table tr.cell:last').index() + 1)
            ;
            $('tbody.list-{{$column}}-table').append(tpl);
            initThemes.call(
                $('.{{ $sectionId }} tbody tr:last-child')
            );
            window.refreshOrdering();
        });

        $(".{{ $sectionId }} * tbody").sortable({
            placeholder: "sort-highlight",
            handle: ".grid-sortable-handle",
            forcePlaceholderSize: true,
            zIndex: 999999
        }).on("sortupdate", function (event, ui) {
            window.refreshOrdering();
        });

        $(document).find('.{{ $sectionId }} .{{ $column }}-remove').each(function () {
            initThemes.call($(this).parents('tr'));
        })
    })();
</script>
