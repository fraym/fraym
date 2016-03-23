<div id="tf-contact" class="text-center">
    <div class="container">

        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="section-title center">
                    <h2>{_('Feel free to contact us')}</h2>
                    <div class="line">
                        <hr>
                    </div>
                    <div class="clearfix"></div>
                </div>
                {if $submit && count((array)$errors) == 0}
                    <div class="alert alert-success bs-alert-old-docs">
                        <strong>{_('Your message has been sent')}!</strong>
                    </div>
                {else}
                    <form action="" method="post">
                        <input type="hidden" name="mailform" value="1"/>
                        <input type="hidden" name="required[msg]" value="1"/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group{if $errors.email} error{/if}">
                                    <label for="email">{_('E-Mail')}</label>
                                    <input required type="email" name="field[email]" class="form-control" value="{$values.field.email}" id="email" placeholder="{_('Enter your email')}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group{if $errors.message} error{/if}">
                            <label for="msg">{_('Message')}</label>
                            <textarea required rows="5" class="form-control" placeholder="{_('Enter your message')}" name="field[msg]" id="msg">{$values.field.msg}</textarea>
                        </div>
                        <button type="submit" class="btn tf-btn btn-default">{_('Submit')}</button>
                    </form>
                {/if}
            </div>
        </div>
    </div>
</div>