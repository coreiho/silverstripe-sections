<% if Sections %>
<div class="sections">
	<% loop Sections.Sort('Sort', ASC) %>
	<%-- AdminTitle = $AdminTitle --%>
	$Layout
	<% end_loop %>
</div>
<% end_if %>
