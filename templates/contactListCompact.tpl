{if count($contacts)}
<table class="contacts_compact">
{foreach from=$contacts item=contact}
	<tr>
		<td>
			<span class="contact_name">{$contact->getName()}</span><br />
			{if $contact->getTitle()}<span class="contact_title">{$contact->getTitle()}</span><br />{/if}
		</td>
		<td>
			{if $contact->getEmail()}<span class="contact_heading">Email:</span> {$contact->getEmail()}<br />{/if}
			{if $contact->getPhone()}<span class="contact_heading">Telephone:</span> <span class="nowrap">{$contact->getPhone()}</span><br />{/if}
			{if $contact->getAddress()}<span class="contact_heading">Address:</span> {$contact->getAddress()}<br />{/if}
		</td>
	</tr>
{/foreach}
</table>
{else}
There are no contacts.
{/if}