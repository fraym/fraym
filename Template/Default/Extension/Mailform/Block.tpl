<div id="contact_form" class="rapid_contact ">

    {if $submit && count((array)$errors) == 0}
        <div class="alert alert-success bs-alert-old-docs">
              <strong>{_('Your message has been sent')}!</strong>
        </div>
    {else}
    <form action="" method="post" class="form-horizontal">
        <input type="hidden" name="mailform" value="1"/>
        <input type="hidden" name="required[name]" value="1"/>
        <input type="hidden" name="required[msg]" value="1"/>

        <div class="form-group{if $errors.name} error{/if}">
            <label for="name">{_('Name')}*</label>

            <div class="controls">
                <input class="form-control" type="text" id="name" name="field[name]" placeholder="{_('Name')}"
                       value="{$values.field.name}">
            </div>
        </div>
        
        <div class="form-group{if $errors.email} error{/if}">
            <label for="email">{_('E-Mail')}*</label>

            <div class="controls">
                <input class="form-control" type="text" placeholder="Email" name="field[email]" id="email"
                       value="{$values.field.email}">
            </div>
        </div>
        
        <div class="form-group">
            <label for="phone">{_('Phone')}</label>

            <div class="controls">
                <input class="form-control" type="text" name="field[phone]" placeholder="{_('Phone')}"
                       id="rp_subject" value="{$values.field.phone}">
            </div>
        </div>
        
        <div class="form-group{if $errors.msg} error{/if}">
            <label for="msg">{_('Message')}*</label>

            <div class="controls">
                <textarea class="form-control" placeholder="{_('Message')}" name="field[msg]" id="msg">{$values.field.msg}</textarea>
            </div>
        </div>
        
        <div class="form-group">
            <div class="controls">
                <input class="btn btn-default" id="submit-form" type="submit" value="{_('Send message')}">
            </div>
        </div>
        
    </form>
    {/if}
</div>