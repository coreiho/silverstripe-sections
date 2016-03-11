<section {$ClassAttr}{$AnchorAttr}>
	here it is
	<% if Title %>
		<h2 class='title'>
			$Title
		</h2>
	<% end_if %>

	<% if Content %>
		<div class='content'>
			$Content
		</div>
	<% end_if %>

	<% if CurrentPageController.SectionForm %>
		<div class="form">
			$CurrentPageController.SectionForm
		</div>
	<% end_if %>
</section>
