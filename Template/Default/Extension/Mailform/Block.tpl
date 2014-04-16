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

        <div class="control-group{if $errors.name} error{/if}">
            <label class="control-label" for="name">{_('Name')}*</label>

            <div class="controls">
                <input class=" inputbox input-xlarge" type="text" id="name" name="field[name]" placeholder="{_('Name')}"
                       value="{$values.field.name}">
            </div>
        </div>
        <!-- end control group -->
        <div class="control-group{if $errors.email} error{/if}">
            <label class="control-label" for="email">{_('E-Mail')}*</label>

            <div class="controls">
                <input class=" inputbox input-xlarge" type="text" placeholder="Email" name="field[email]" id="email"
                       value="{$values.field.email}">
            </div>
        </div>
        <!-- end control group -->
        <div class="control-group">
            <label class="control-label" for="phone">{_('Phone')}</label>

            <div class="controls">
                <input class=" inputbox input-xlarge" type="text" name="field[phone]" placeholder="{_('Phone')}"
                       id="rp_subject" value="{$values.field.phone}">
            </div>
        </div>
        <!-- end control group -->
        <div class="control-group{if $errors.msg} error{/if}">
            <label class="control-label" for="msg">{_('Message')}*</label>

            <div class="controls">
                <textarea class=" textarea span4" placeholder="{_('Message')}" name="field[msg]" id="msg">{$values.field.msg}</textarea>
            </div>
        </div>
        <!-- end control group -->
        <div class="control-group">
            <div class="controls">
                <input class=" btn " id="submit-form" type="submit" value="{_('Send message')}">
            </div>
        </div>
        <!-- end control group -->
    </form>
    {/if}
</div>