{extends file='frontend/index/index.tpl'}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_content'}
    <div class="grid_20 password">

        {* Error messages *}
        {block name="frontend_account_error_messages"}
            {if $sErrorMessages}
                <div class="grid_20 error_msg" style="margin-left:7px;">
                    {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
                </div>
            {/if}
        {/block}

        {* New password panel *}
        {block name='frontend_account_password_new_content'}
            <div class="grid_20" style="margin-left:7px;">

                {* New password panel title *}
                {block name='frontend_account_password_new_title'}
                {/block}

                {* New password form *}
                {block name='frontend_account_password_new_form'}
                    <form action="{url controller=Password action=resetPassword}" method="post">
                        {block name='frontend_account_password_new'}
                            <h2 class="headingbox_dark largesize">{s name="restorePasswordHeadline" namespace="frontend/restorePassword/index"}{/s}</h2>
                            <div class="outer">

                                {* New password fields *}
                                {block name='frontend_account_password_new_fields'}
                                    <fieldset>

                                        {* Secret hash hidden input *}
                                        {block name='frontend_account_password_new_hash_input'}
                                            <input name="hash"
                                                   value="{$hash}"
                                                   type="hidden"
                                                   id="hash"
                                                   class="password-new--input input--hash{if $sErrorFlag.hash} has--error{/if}">
                                        {/block}

                                        {* New password input *}
                                        {block name='frontend_account_password_new_password_input'}
                                            <p>
                                                <label>
                                                    {s name="restorePasswordNewPassword" namespace="frontend/restorePassword/index"}{/s}{s name="Star" namespace="frontend/listing/box_article"}*{/s}
                                                </label>
                                                <input name="password" type="password" id="newpwd" class="text" /><br />
                                            </p>
                                        {/block}

                                        {* New password confirmation input *}
                                        {block name='frontend_account_password_new_password_confirmation_input'}
                                            <p>
                                                <label>
                                                    {s name="restorePasswordNewPasswordRepeat" namespace="frontend/restorePassword/index"}{/s}{s name="Star" namespace="frontend/listing/box_article"}*{/s}
                                                </label>
                                                <input name="passwordConfirmation" type="password" id="newpwdrepeat" class="text" /><br />
                                            </p>
                                        {/block}
                                    </fieldset>
                                {/block}

                                {* New password helptext *}
                                {block name='frontend_account_password_new_helptext'}
                                    <p class="password-new--helptext">
                                        {s name="restorePasswordNewPasswordHelp" namespace="frontend/restorePassword/index"}{/s}
                                    </p>
                                {/block}

                                {* New password actions *}
                                {block name='frontend_account_password_new_password_actions'}
                                    <p class="buttons">
                                        <input type="submit" name="AccountLinkChangePassword" class="button-right large" value="{s name="restorePasswordLinkChange" namespace="frontend/restorePassword/index"}{/s}" />
                                        <div class="clear">&nbsp;</div>
                                    </p>
                                {/block}
                            </div>
                        {/block}
                    </form>
                {/block}
            </div>
        {/block}
    </div>
{/block}