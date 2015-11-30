{* Recover password form *}
{block name='frontend_account_password_form'}
	<form name="frmRegister" method="post" action="{url controller=Password action=password}">
		<h2 class="headingbox_dark largesize">{s name="restorePasswordHeader" namespace="frontend/restorePassword/index"}{/s}</h2>
		<div class="outer">
			<fieldset>
				<p>
					<label>{s name="restorePasswordLabelMail" namespace="frontend/restorePassword/index"}{/s}</label>
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