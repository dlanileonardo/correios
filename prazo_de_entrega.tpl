<p class="prazo-de-entrega-container">
    {if $dias == 0}
        <strong>{l s="Não foi possível estipular o prazo de entrega no momento." mod="correios"}<strong>
    {else}
        <strong>{$nomeServico}:</strong>
        {l s="O Prazo de entrega é de " mod="correios"}<strong>{$dias}</strong>
        {if $dias == 1}
            {l s=" dia após a confirmação do pagamento." mod="correios"}
        {else}
            {l s=" dias após a confirmação do pagamento." mod="correios"}
        {/if}
    {/if}
</p>