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
{if isset($hideFormElements)}
	<input type="hidden" value="{$hideFormElements|escape|trim}" class="hideFormElements">
{/if}
<link rel="stylesheet" type="text/css" href="{$enhMetaDataStyle}">
<script src="{$enhMetaDataScript}" type="text/javascript" defer></script>
{*{fbvFormSection title="plugins.generic.enhanced.metadata.submission.title" class="enhanced-metadata"}
	<p class="description">{translate key="plugins.generic.enhanced.metadata.submission.description"}</p>
{/fbvFormSection}*}
{fbvFormArea class="enhanced-metadata-form"}
{foreach from=$enhFormFields item=$itm}
    {if $itm['type']=='text' || $itm['type']=='textarea'}
		<div {if $itm['condition']}class="section em-hidden-field {$itm['class']}" data-condition="{json_encode($itm['condition'])|escape|trim}"
             {else}class="section {$itm['class']}"{/if}>
			<label class="{$itm['title']['class']}">
                {if $itm['title'][$currentLocale]}
                    {$itm['title'][$currentLocale]|trim}
                {else}
                    {$itm['title']['en_US']|trim}
                {/if}
                {if $itm['required']}
					&nbsp;
					<span class="req">*</span>
                {/if}
				<label class="description {$itm['description']['class']}">
                    {if $itm['description'][$currentLocale]}
                        {$itm['description'][$currentLocale]|trim}
                    {else}
                        {$itm['description']['en_US']|trim}
                    {/if}
				</label>
                {foreach from=$itm['fields'] item=$field}
                    {if !$field['size'] || $field['size']|upper == 'LARGE'}
                        {assign var="fbvSize" value=$fbvStyles.size.LARGE}
                    {elseif $field['size']|upper == 'MEDIUM'}
                        {assign var="fbvSize" value=$fbvStyles.size.MEDIUM}
                    {elseif $field['size']|upper == 'SMALL'}
                        {assign var="fbvSize" value=$fbvStyles.size.SMALL}
                    {/if}
                    {fbvElement type=$itm['type'] multilingual=$field['multilingual'] id=$field['name'] value=$enhMetaDataJson[$field['name']] rich=$field['rich']
                    required=$field['required'] maxlength=$field['maxLength'] class=$field['class'] size=$fbvSize}
                {/foreach}
			</label>
		</div>
    {elseif $itm['type']=='radio' || $itm['type']=='checkbox'}
		<div {if $itm['condition']}class="section em-hidden-field {$itm['class']}" data-condition="{json_encode($itm['condition'])|escape|trim}"
             {else}class="section {$itm['class']}"{/if}>
            {if $itm['title'] && ($itm['title'][$currentLocale] || $itm['title']['en_US'])}
				<span class="label {$itm['title']['class']}">
					{if $itm['title'][$currentLocale]}
                        {$itm['title'][$currentLocale]|trim}
                    {else}
                        {$itm['title']['en_US']|trim}
                    {/if}
				</span>
            {/if}
            {if $itm['description'] &&($itm['description'][$currentLocale]  || $itm['description']['en_US'])}
				<label class="description {$itm['description']['class']}">
                    {if $itm['description'][$currentLocale]}
                        {$itm['description'][$currentLocale]|trim}
                    {else}
                        {$itm['description']['en_US']|trim}
                    {/if}
				</label>
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
                            {if $field['desc'][$currentLocale]}
                            {$field['desc'][$currentLocale]|trim}
                            {else}
                            {$field['desc']['en_US']|trim}
                            {/if}
						</label>
					</li>
                {/foreach}
			</ul>
		</div>
    {elseif $itm['type']=='headline'}
		<div class="section {$itm['class']}">
            {if $itm['title'] && ($itm['title'][$currentLocale] || $itm['title']['en_US'])}
				<label class="{$itm['title']['class']}">
                    {if $itm['title'][$currentLocale]}
                        {$itm['title'][$currentLocale]|trim}
                    {else}
                        {$itm['title']['en_US']|trim}
                    {/if}
				</label>
            {/if}
            {if $itm['description'] && ($itm['description'][$currentLocale] || $itm['description']['en_US'])}
				<p class="{$itm['description']['class']}">
                    {if $itm['description'][$currentLocale]}
                        {$itm['description'][$currentLocale]|trim}
                    {else}
                        {$itm['description']['en_US']|trim}
                    {/if}
				</p>
            {/if}
		</div>
    {/if}
{/foreach}
{/fbvFormArea}
