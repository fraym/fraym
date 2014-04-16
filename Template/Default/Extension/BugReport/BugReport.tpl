
<form action="" method="post" autocomplete="off">
    {if count((array)$result)}
        <div class="form-group">
            <div class="alert alert-success">
                {$result.message}
            </div>
        </div>
    {/if}
    <div class="form-group{if $errors.name} error{/if}">
        <label for="name">{_('Your name')}*</label>
        <input class="form-control" type="text" id="name" name="field[name]" placeholder="{_('Your name')}" value="{$values.field.name}" required/>
    </div>

    <div class="form-group{if $errors.email} error{/if}">
        <label for="email">{_('Your e-mail')}*</label>
        <input class="form-control" type="email" id="email" name="field[email]" placeholder="{_('E-Mail')}" value="{$values.field.email}" required/>
    </div>

    <div class="form-group{if $errors.subject} error{/if}">
        <label for="Subject">{_('Subject')}*</label>
        <input class="form-control" type="text" id="Subject" name="field[subject]" placeholder="{_('Subject')}" value="{$values.field.subject}" required/>
    </div>

    <div class="form-group{if $errors.description} error{/if}">
        <label for="description">{_('Error description')}*</label>
        <textarea style="height:100px;" class="form-control" type="text" id="description" name="field[description]" placeholder="{_('Error description')}" required>{$values.field.description}</textarea>
    </div>

    <div class="form-group{if $errors.reproduce} error{/if}">
        <label for="reproduce">{_('Steps to reproduce')}*</label>
        <textarea style="height:100px;" class="form-control" type="text" id="reproduce" name="field[reproduce]" placeholder="{_('Steps to reproduce')}" required>{$values.field.reproduce}</textarea>
    </div>

    <p>{_('The bug report will send also information about your system.')}</p>
    <input class=" btn " id="submit-form" type="submit" value="{_('Send bug report')}">
</form>
