{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors#Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
*
* @author Samdha <contact@samdha.net>
* @copyright  Samdha
* @license    commercial license see license.txt
*}
{if $registered}
    {if $content_html}
        <fieldset class="{if !$bootstrap}ui-widget ui-widget-content ui-corner-all{/if}{if $space} space{/if}">{$content_html}</fieldset>
    {/if}
{else}
    <fieldset class="{if !$bootstrap}ui-widget ui-widget-content ui-corner-all{/if}{if $space} space{/if}" id="samdha_registerform">
        <legend class="{if !$bootstrap}ui-widget-header ui-corner-all{/if}">{samdha_l s='Register this module' mod='samdha'}</legend>
        <p>{samdha_l s='By register your module you will get:' mod='samdha'}</p>
        <ul>
            <li>{samdha_l s='Faster and better support,' mod='samdha'}</li>
            <li>{samdha_l s='Latest version before everyone,' mod='samdha'}</li>
            <li>{samdha_l s='Automatic update of the module.' mod='samdha'}</li>
        </ul>
        <p>
            {samdha_l s='Just fill' mod='samdha'} <a style="text-decoration: underline;" href="{$licence_url|escape:'htmlall':'UTF-8'}" target="_blank" class="module_support">{samdha_l s='this form' mod='samdha'}</a>{samdha_l s=', it\'s free.' mod='samdha'}
        </p>
        <hr/>
        <form action="{$module_url|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
            <p style="font-size: 0.85em;">
                <label for="licence_number" class="t">{samdha_l s='Licence number:' mod='samdha'}</label><br/>
                <input style="display: inline-block; width: 200px" type="text" name="licence_number" id="licence_number" value="{$licence_number|escape:'htmlall':'UTF-8'}"/>
                <button class="ui-button-primary" name="saveLicence" value="1" style="margin-top: -.1em;">{samdha_l s='Ok' mod='samdha'}</button>
            </p>
        </form>
    </fieldset>
{/if}
