{**
 * plugins/generic/enhancedMetadata/submissionMetaData.tpl
 *
 * Copyright (c) 2015-2019 University of Pittsburgh
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2020 Ronny Bölter, ZPID
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Enhanced Metadata Submission Form
 *
 *}

<style>
	.hidden-field {
		display: none;
	}

	.em-inline {
		display: inline-flex;
		margin-right: 15px;
	}

	.em-font-weight-normal {
		font-weight: normal !important;
	}

	.em-font-14px {
		font-size: 14px !important;
	}

	.em-section-radio {

	}
	.em-margin-bottom-60 {
		margin-bottom: 60px !important;
	}
	.em-margin-bottom-10 {
		margin-bottom: 10px !important;
	}

	.em-text-center{
		text-align: center;
	}

	.em-font-italic{
		font-style: italic;
	}

	.em-border-bottom{
		border-bottom: 1px solid black;
	}

	.em-margin-left{
		margin-left: 20px;
	}

</style>

{fbvFormSection title="plugins.generic.enhanced.metadata.submission.title" class="enhanced-metadata"}
	<p class="description">{translate key="plugins.generic.enhanced.metadata.submission.description"}</p>
{/fbvFormSection}
{fbvFormArea id="enhanced-metadata-form"}
{foreach from=$enhFormFields item=$itm}
    {if $itm['type']=='text' || $itm['type']=='textarea'}
		<div {if $itm['condition']}class="section hidden-field {$itm['class']}" data-condition="{json_encode($itm['condition'])|escape|trim}"
             {else}class="section {$itm['class']}"{/if}>
			<label class="{$itm['title']['class']}">{$itm['title'][$currentLocale]|escape|trim}{if $itm['required']}&nbsp;<span class="req">*</span>{/if}
				<label class="description {$itm['description']['class']}">{$itm['description'][$currentLocale]|escape|trim}</label>
                {foreach from=$itm['fields'] item=$field}
					<div>
                        {fbvElement type=$itm['type'] multilingual=$field['multilingual'] id=$field['name'] value=$enhMetaDataJson[$field['name']] rich=$field['rich']
                        required=$field['required'] maxlength=$field['maxLength'] class=$field['class']}
					</div>
                {/foreach}
			</label>
		</div>
    {elseif $itm['type']=='radio' || $itm['type']=='checkbox'}
		<div {if $itm['condition']}class="section hidden-field {$itm['class']}" data-condition="{json_encode($itm['condition'])|escape|trim}"
             {else}class="section {$itm['class']}"{/if}>
            {if $itm['title'] && $itm['title'][$currentLocale] && $itm['title'][$currentLocale]|trim != ''}
				<span class="label {$itm['title']['class']}">{$itm['title'][$currentLocale]|trim}</span>
            {/if}
            {if $itm['description'] && $itm['description'][$currentLocale] && $itm['description'][$currentLocale]|trim != ''}
				<label class="description {$itm['description']['class']}">{$itm['description'][$currentLocale]|escape|trim}</label>
            {/if}
			<ul class="checkbox_and_radiobutton">
                {foreach from=$itm['fields'] item=$field}
                    {assign var="uuid" value=""|uniqid|escape}
					<li class="{if $itm['inline']}em-inline{/if}">
						<label>
                            {if $itm['type']=='radio'}
                            {assign var="itmName" value=$itm['name']}
                            {else}
                            {assign var="itmName" value=$field['name']}
                            {/if}
							<input type="{$itm['type']}" id="{$itmName}-{$uuid}" value="{$field['value']}" name="{$itmName}"
							       class="field {$field['class']} {$itm['type']}{if $field['required']} required" validation="required"{else}"{/if}
                            {if $field['value'] == $enhMetaDataJson[$itmName]} checked{elseif !$enhMetaDataJson[$itmName] && $field['selected']}checked{/if}>
                            {$field['desc'][$currentLocale]|escape|trim}
						</label>
					</li>
                {/foreach}
			</ul>
		</div>
    {elseif $itm['type']=='headline'}
		<div class="section {$itm['class']}">
            {if $itm['title'] && $itm['title'][$currentLocale] && $itm['title'][$currentLocale]|trim != ''}
				<label class="{$itm['title']['class']}">{$itm['title'][$currentLocale]|escape|trim}</label>
            {/if}
            {if $itm['description'] && $itm['description'][$currentLocale] && $itm['description'][$currentLocale]|trim != ''}
				<p class="{$itm['description']['class']}">{$itm['description'][$currentLocale]|escape|trim}</p>
            {/if}
		</div>
    {/if}
{/foreach}
{/fbvFormArea}

<script>
	document.querySelectorAll('[data-condition]').forEach(field => {
		let condition = JSON.parse(field.getAttribute('data-condition'));
		collect(field, condition.item, condition.value);
	});

	function collect(elem, c_name, c_value) {
		document.querySelectorAll('input[name="' + c_name + '"]').forEach(c_elem => {
			if (c_elem) {
				switch (c_elem.type) {
					case  'radio':
					case 'checkbox':
						if (c_elem.checked && c_elem.value === '' + c_value) {
							elem.classList.remove('hidden-field');
							elem.classList.add('em-margin-left');
						}
						c_elem.addEventListener('click', function () {
							checkboxListener(elem, c_elem, c_value);
						});
						break;
				}
			}
		});
	}

	function checkboxListener(elem, c_elem, c_value) {
		if (c_elem.checked && c_elem.value === '' + c_value) {
			elem.classList.remove('hidden-field');
			elem.classList.add('em-margin-left');
		}
		else {
			elem.classList.add('hidden-field');
			elem.classList.remove('em-margin-left');
		}
	}
</script>