{foreach from=$contacts item=contact}
<div class="contact_container">
	<div class="contact_item">
		<span class="contact_name">{$contact->getName()}</span><br />
		{if $contact->getTitle()}<span class="contact_title">{$contact->getTitle()}</span><br />{/if}
		<table class="contact">
			{if $contact->getEmail()}<tr><td width="140px"><span class="contact_heading">Email:</span></td><td>{$contact->getEmail()}</td></tr>{/if}
			{if $contact->getPhone()}<tr><td width="140px"><span class="contact_heading">Telephone Number:</span></td><td>{$contact->getPhone()}</td></tr>{/if}
			{if $contact->getAddress()}<tr><td width="140px"><span class="contact_heading">Address:</span></td><td>{$contact->getAddress()}</td></tr>{/if}
		</table>
	</div>
</div><br />
{foreachelse}
There are no contacts.
{/foreach}