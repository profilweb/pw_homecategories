{**
 * Blocks Categories on HomePage: module for PrestaShop.
 *
 * @author    profilweb. <manu@profil-web.fr>
 * @copyright 2021 profil Web.
 * @link      https://github.com/profilweb/pw_homecategories The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE pw_homecategories -->
<div id="pw-homecategories">
    <h4>{l s='Popular categories' mod='Pwhomecategories'}</h4>
    <ul class="row">
        {foreach from=$categories item=category name=homeCategory}
            {assign var='categoryLink' value= $link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value= $link->getCatImageLink($category->link_rewrite, $category->id_category, 'category_default')}
            
            <li class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
                <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                    {if $category->id_image|intval > 0}
                        <img src="{$imageLink}" alt="{$category->name|escape:html:'UTF-8'}" />
                    {else}
                        <img src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg" alt="{$category->name|escape:html:'UTF-8'}" />
                    {/if}
                </a>
                <h5 class="category-title">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {$category->name|escape:html:'UTF-8'}
                    </a>
                </h5>
                <p class="category-description">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {$category->description|strip_tags|stripslashes|escape:html:'UTF-8'}
                    </a>
                </p>
            </li>
        {foreachelse}
            {l s='No categories' mod='Pwhomecategories'}
        {/foreach}
    </ul>
</div>
<!-- /MODULE pw_homecategories -->