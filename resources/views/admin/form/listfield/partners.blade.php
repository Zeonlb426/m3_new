@php
    use App\Admin\Controllers\Api\DropDownListLoaderController;
    use App\Enums\LoaderType;
    use App\Enums\MorphMapperTarget;
@endphp
@php($listErrorKey = "partners")
@php($sectionId = 'partner')

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
                    class="partners_block_enabled"
                    value="1"
                    {{ ($titles_content['section_enabled'][MorphMapperTarget::PARTNER->value] ?? null) ? 'checked' : '' }}
                    name="titles_content[section_enabled][{{ MorphMapperTarget::PARTNER }}]"
                />
            </div>
            <div class="col-sm-3 text-right">
                <label class="control-label">
                    {{ \__('admin.models.competition.partners.pivot.titles_content.section_name') }}
                </label>
            </div>
            <div class="col-sm-6">
                <input
                    name="titles_content[section_name][{{ MorphMapperTarget::PARTNER }}]"
                    class="form-control"
                    value="{{ $titles_content['section_name'][MorphMapperTarget::PARTNER->value] ?? null }}"
                />
            </div>

            <div class="col-sm-12 text-right">
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle"></i>
                    &nbsp;
                    {!! \__('admin.models.competition.partners.pivot.titles_content.default') !!}
                </small>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
            <tr>
                <td colspan="3">
                    <div class="row">
                        <div class="col-sm-1">#</div>
                        <div class="col-sm-4">
                            {{ \__('admin.models.competition.partners.pivot.is_main') }}
                        </div>
                        <div class="col-sm-5">
                            {{ \__('admin.models.competition.partners.title') }}
                        </div>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody class="list-{{$column}}-table">

            @foreach($value ?: [] as $k => $v)
                    <?php
                    /* @var $v \App\Models\Competition\Partner */ ?>

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
                            <div class="form-group">
                                <div class="col-sm-2 text-center">

                                    <label>
                                        &nbsp;
                                        <input
                                            class="iradio_flat-red"
                                            type="radio"
                                            data-id="{{ $v->getKey() }}"
                                            value="{{ $v->_pivot_is_main ? $v->getKey() : '' }}"
                                            {{ $v->_pivot_is_main ? 'checked' : '' }}
                                            name="{{ $column }}[is_main]"
                                            required
                                        />
                                        &nbsp;
                                    </label>
                                </div>
                                <div class="col-sm-10">
                                    <select name="{{ $column }}[{{ $v->getKey() }}][id]" class="form-control"
                                            style="width: 100%">
                                        <option value="{{ $v->getKey() }}" selected>{{ $v->title }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-10">
                                    <br>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-addon">
                                            <i class="fa fa-text-height"></i>
                                        </div>

                                        <input
                                            name="{{ $column }}[{{ $v->getKey() }}][partner_text]"
                                            class="form-control"
                                            placeholder="{{ \__('admin.models.competition.partners.pivot.titles_content.partner_text') }}"
                                            value="{{( $v->_pivot_titles_content ?: [])['partner_text'] ?? null }}"
                                        />
                                    </div>
                                </div>
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
                <input type="hidden" name="{{ $column }}[{id}][order_column]" value="{pos}">
            </td>
            <td>
                <div class="form-group">
                    <div class="col-sm-2 text-center">
                        <label>
                            &nbsp;
                            <input class="iradio_flat-red" type="radio" data-id="{id}" name="{{ $column }}[is_main]"
                                   required>
                            &nbsp;
                        </label>
                    </div>
                    <div class="col-sm-10">
                        <select name="{{ $column }}[{id}][id]" class="form-control" required style="width: 100%">
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10">
                        <br>
                        <div class="input-group input-group-sm">
                            <div class="input-group-addon">
                                <i class="fa fa-text-height"></i>
                            </div>

                            <input name="{{ $column }}[{id}][partner_text]" class="form-control"
                                   placeholder="{{ \__('admin.models.competition.partners.pivot.titles_content.partner_text') }}"/>
                        </div>
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

        document.new_partners = 0;

        let initPartners = function () {
            $('input[type="radio"]', $(this)).each(function () {
                $(this).uncheckableRadio()
            });

            $('select', $(this)).select2({
                ajax: {
                    url: "{{ \route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::PARTNERS->value]) }}",
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
                placeholder: "{{ \__('admin.models.competition.partners.title') }}",
                minimumInputLength: 0,
                escapeMarkup: function (markup) {
                    return markup;
                }
            })
        };

        $('.partners_block_enabled').bootstrapSwitch({
            size: 'small',
            onText: '{{ \__('admin.switch_block_visible_statuses.on.text') }}',
            offText: '{{  \__('admin.switch_block_visible_statuses.off.text') }}',
            onColor: '{{  \__('admin.switch_block_visible_statuses.on.color') }}',
            offColor: '{{  \__('admin.switch_block_visible_statuses.off.color') }}'
        });

        $('.{{$column}}-add').off('click').on('click', function () {
            let tpl = $('template.{{$column}}-tpl').html();

            tpl = tpl
                .replaceAll('{id}', 'new_' + (document.new_partners++))
                .replaceAll('{pos}', $('tbody.list-{{$column}}-table').find('tr.cell').length + 1)
            ;
            $('tbody.list-{{$column}}-table').append(tpl);
            initPartners.call(
                $('.{{ $sectionId }} tbody tr:last-child')
            );
            window.refreshOrdering();
        });
        $('.{{$column}}-remove').off('click').on('click', function () {
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
            initPartners.call($(this).parents('tr'));
        });
    })();
</script>
