<% if LinkURL %>
    <a href="{$LinkURL}"{$TargetAttr}{$ClassAttr}>
        {$Title}
    </a>
    <% if Children %>
        <ul>
            <% loop Children %>
                <li>
                    {$Me}
                </li>
            <% end_loop %>
        </ul>
    <% end_if %>
<% end_if %>
