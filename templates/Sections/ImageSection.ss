<section {$ClassAttr}{$AnchorAttr}>
	<% loop Images %>
		<img src="{$Fill(200, 200).URL}" alt="{$Title}" width="{$Fill(200, 200).Width}" height="{$Fill(200, 200).Height}" />
	<% end_loop %>
</section>
