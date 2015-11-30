{extends file='frontend/index/index.tpl'}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="grid_20 password">

        {* Error messages *}
        {block name='frontend_account_error_messages'}
            {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
        {/block}

        {* Success message *}
        {if $sSuccess}
            {block name='frontend_account_password_success'}
                <div class="success">
                    <strong>{s name="restorePasswordInfoSuccess" namespace="frontend/restorePassword/index"}{/s}</strong>
                </div>
                <p>
                    <a href="javascript:history.back();" class="button-left large"><span>{s name="restorePasswordLinkBack" namespace="frontend/restorePassword/index"}{/s}</span></a>
                </p>
            {/block}
        {else}
            {* Recover password form *}
            {block name='frontend_account_password_form'}
                <form name="frmRegister" method="post" action="{url controller=Password action=password}">
                    <h2 class="headingbox_dark largesize">{s name="restorePasswordHeader" namespace="frontend/restorePassword/index"}{/s}</h2>
                    <div class="outer">
                        <fieldset>
                            <p>
                                <label>{se name="SwagPasswordRestorePasswordLabelMail"}{/se}</label>
                                <input name="email" type="text" id="txtmail" class="text" /><br />
                            </p>
                            <p class="description">{s name="restorePasswordText" namespace="frontend/restorePassword/index"}{/s}</p>
                        </fieldset>

                        <p class="buttons">
                            <a href="javascript:history.back();" class="button-left large">{s name="restorePasswordLinkBack" namespace="frontend/restorePassword/index"}{/s}</a>
                            <input type="submit" class="button-right large" value="{s name="restorePasswordSendAction" namespace="frontend/restorePassword/index"}{/s}" />
                        <div class="clear">&nbsp;</div>
                        </p>
                    </div>
                </form>
            {/block}
        {/if}
    </div>
    <div class="doublespace">&nbsp;</div>
{/block}