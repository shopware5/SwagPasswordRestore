{block name="frontend_account_index_success_messages"}
    {if $sSuccessAction}
        <div class="success bold center grid_16">
            {if $sSuccessAction == 'billing'}
                {s name="AccountBillingSuccess" namespace="frontend/account/success_messages"}{/s}
            {elseif $sSuccessAction == 'shipping'}
                {s name="AccountShippingSuccess" namespace="frontend/account/success_messages"}{/s}
            {elseif $sSuccessAction == 'payment'}
                {s name="AccountPaymentSuccess" namespace="frontend/account/success_messages"}{/s}
            {elseif $sSuccessAction == 'account'}
                {s name="AccountAccountSuccess" namespace="frontend/account/success_messages"}{/s}
            {elseif $sSuccessAction == 'newsletter'}
                {s name="AccountNewsletterSuccess" namespace="frontend/account/success_messages"}{/s}
            {elseif $sSuccessAction == 'optinnewsletter'}
                {s name="sMailConfirmation" namespace="frontend"}{/s}
            {elseif $sSuccessAction == 'deletenewsletter'}
                {s name="NewsletterMailDeleted" namespace="frontend/account/internalMessages"}{/s}
            {elseif $sSuccessAction == 'resetPassword'}
                {s name="passwordResetSuccess" namespace="frontend/restorePassword/index"}{/s}
            {/if}
        </div>
    {/if}
{/block}