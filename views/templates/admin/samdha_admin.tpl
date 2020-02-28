{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors
* #Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
*
* @author Samdha <contact@samdha.net>
* @copyright  Samdha
* @license    commercial license see license.txt
*}
<div id="samdha_wait"><img src="../img/loader.gif"/></div>
{if $version_16 && $bootstrap}<div class="container-fluid">{/if}
<div id="samdha_warper" class="{if $version_16}ps16{else}{if $version_15}ps15{else}ps14{/if}{/if} {if $version_16 && $bootstrap}row{/if}" style="visibility: hidden">
    <div id="samdha_content" class="{if $version_16}ps16{else}{if $version_15}ps15{else}ps14{/if}{/if}">
        <div id="samdha_tab">
            <ul>
                {if isset($tabs) && is_array($tabs)}
                    {foreach from=$tabs item=tab}
                        <li>
                            <a href="{$tab.href|escape:'htmlall':'UTF-8'}" {if isset($tab.rel)}rel="{$tab.rel|escape:'htmlall':'UTF-8'}"{/if} {if isset($tab.id)}id="{$tab.id|escape:'htmlall':'UTF-8'}"{/if} {if isset($tab.title)}id="{$tab.title|escape:'htmlall':'UTF-8'}"{/if}>
                                <span> {$tab.display_name|escape:'htmlall':'UTF-8'}</span>
                            </a>
                        </li>
                    {/foreach}
                {/if}
                <li><a rel="iframe" id="tabHelp" href="{$documentation_url|escape:'htmlall':'UTF-8'}">
                    <span> {l s='Documentation' mod='samdha'}</span>
                </a></li>
                <li><a id="tabAbout" href="#samdha_about">
                    <span> {l s='About' mod='samdha'}</span>
                </a></li>
                {if $version_16 && ((isset($rate_url) && $rate_url) || (isset($products_url) && $products_url))}
                    <div class="samdha_adv">
                        {if isset($support_url) && $support_url}
                            <a class="samdha_support" target="_blank" href="{$support_url|escape:'htmlall':'UTF-8'}">{l s='Support' mod='samdha'}</a>
                        {/if}
                        {if isset($rate_url) && $rate_url}
                            <a class="samdha_rating" target="_blank" href="{$rate_url|escape:'htmlall':'UTF-8'}">{l s='Rate this module' mod='samdha'}</a>
                        {/if}
                        {if isset($products_url) && $products_url}
                            <a class="samdha_products" target="_blank" href="{$products_url|escape:'htmlall':'UTF-8'}">{l s='Other modules' mod='samdha'}</a>
                        {/if}
                    </div>
                {/if}
            </ul>
            {if $content}
                {include file=$content}
            {/if}
            <div id="samdha_about" class="col-lg-10 col-md-9">
                <div class="panel">
                    <h3 class="tab"> <i class="icon-info"></i> {l s='About' mod='samdha'}</h3>
                    {$about_form_html}
                </div>
            </div>
        </div>
    </div>
</div>
{if $version_16 && $bootstrap}</div>{/if}
<br style="clear: both"/>
<script type="text/javascript">
    var module = {ldelim}
        active_tab: '{$active_tab|escape:'javascript':'UTF-8'}',
        module_url: '{$module_url|escape:'javascript':'UTF-8'}',
        module_path: '{$module_path|escape:'javascript':'UTF-8'}',
        short_name: '{$module_short_name|escape:'javascript':'UTF-8'}',
        version_15: '{$version_15|escape:'javascript':'UTF-8'}',
        version_16: '{$version_16|escape:'javascript':'UTF-8'}'
    {rdelim};
    var messages = {ldelim}
    {rdelim};
</script>
{if $version_16 && $bootstrap}
    <link rel="stylesheet" type="text/css" href="{$vendor_path|escape:'htmlall':'UTF-8'}views/css/jquery-ui-1.10.3.custom.css">
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="{$vendor_path|escape:'htmlall':'UTF-8'}views/css/jquery.ui.1.10.3.ie.css">
    <![endif]-->
{else}
    <link rel="stylesheet" type="text/css" href="//ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/themes/smoothness/jquery-ui.css">
{/if}
<link rel="stylesheet" type="text/css" href="{$vendor_path|escape:'htmlall':'UTF-8'}views/css/samdha_admin.css?v={$module_version|escape:'htmlall':'UTF-8'}">
{if !$version_16}
    <script src="{$vendor_path|escape:'htmlall':'UTF-8'}views/js/jquery-1.9.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{$vendor_path|escape:'htmlall':'UTF-8'}views/css/jquery.chosen.css">
    <script src="{$vendor_path|escape:'htmlall':'UTF-8'}views/js/jquery.chosen.js?v={$module_version|escape:'htmlall':'UTF-8'}" type="text/javascript"></script>
{/if}
<script src="{$vendor_path|escape:'htmlall':'UTF-8'}views/js/jquery-ui-1.10.4.min.js"></script>
{if $footer}
    {include file=$footer}
{/if}
<script src="{$vendor_path|escape:'htmlall':'UTF-8'}views/js/samdha_admin.js?v={$module_version|escape:'htmlall':'UTF-8'}" type="text/javascript"></script>
{if $admin_js}
    <script src="{$module_path|escape:'htmlall':'UTF-8'}views/js/admin.js?v={$module_version|escape:'htmlall':'UTF-8'}" type="text/javascript"></script>
{/if}
