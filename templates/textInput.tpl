<div class="section">
    {assign var="uuid" value=""|uniqid|escape}
	<label for="{$itm['name']}-{$uuid}">{$itm['title']['de_DE']}</label>
	<label class="description">{$itm['description']['de_DE']}</label>
	<div>
        {fbvElement type=$itm['type'] multilingual=true id=$itm['name'].$uuid value=$itm['value']}
	</div>
</div>