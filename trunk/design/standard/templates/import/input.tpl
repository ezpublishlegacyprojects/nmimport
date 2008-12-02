<h1>{"Import data"|i18n('import')}</h1>


{if $errors}
<div class="message-error">

<h2>Error in import data</h2>

<p>One or more of the lines in the import data contains errors. The errors need to be corrected before you attempt to import again.</p>

<p>The following lines contains errors:</p>

<ul>

	{foreach $errors as $index => $error}

	<li>Line no. {$index|sum(1)}: {foreach $error as $msg}{$msg} {/foreach}</li>

	{/foreach}

</ul>

<p><strong>NOTE: Nothing will be imported until these errors are fixed.</strong></p>

</div>
{/if}

{if $success}
<div class="message-feedback">

<h2>Objects stored</h2>

<p>The following lines were stored as objects:</p>

<ul>

	{foreach $success as $index => $error}

	<li>Line no. {$index|sum(1)}: {foreach $error as $msg}{$msg} {/foreach}</li>

	{/foreach}

</ul>

</div>
{/if}

{if $warnings}
<div class="message-warning">

<h2>Objects not stored</h2>

<p>The following lines were not stored due to the reasons stated below:</p>

<ul>

	{foreach $warnings as $index => $error}

	<li>Line no. {$index|sum(1)}: {foreach $error as $msg}{$msg} {/foreach}</li>

	{/foreach}

</ul>

</div>
{/if}




<p>{"Please copy and paste the contents of an Excel spreadsheet consisting of columns of data."|i18n('import')}</p>

<form action={"import/input"|ezurl} method="post">

	<div class="block">
		<label>{"Import format:"|i18n('import')}</label>
	
		<select name="ImportFormat">
			{foreach $import_format_list as $import_format}
			<option value="{$import_format}">{$import_format}</option>
			{/foreach}
		</select>
	</div>

	<textarea name="ImportData" cols="80" rows="20" id="importdata"></textarea>

	<p><input type="checkbox" name="IgnoreFirstLine" value="1" /> {"First line contains labels (and not data)."|i18n('import')}</p>

	<div class="right">
		<input type="submit" name="Import" value="{"Import."|i18n('import')} &gt;&gt;" id="nextbutton" />
	</div>

</form>