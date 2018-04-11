<div id="content" class="content">
<table class="table">
	<tr>
		<td class="c" colspan="4">Служба техподдержки</td>
	</tr>
	{% if list|length > 0 %}
		<tr>
			<th style="width:10%">ID</th>
			<th style="width:50%">Тема</th>
			<th style="width:15%">Статус</th>
			<th style="width:25%">Дата</th>
		</tr>
		{% for item in list %}
			<tr>
				<td class="c">{{ item['id'] }}</td>
				<td class="c"><a href="javascript:;" onclick="ShowHiddenBlock('ticket_{{ item['id'] }}');">{{ item['subject'] }}</a></td>
				<td class="c">
					{% if item['status'] == 0 %}
						<span style="color:red">закрыто</span>
					{% elseif item['status'] == 1 %}
						<span style="color:green">открыто</span>
					{% elseif item['status'] == 2 %}
						<span style="color:orange">ответ админа</span>
					{% elseif item['status'] == 3 %}
						<span style="color:green">ответ игрока</span>
					{% endif %}
				</td>
				<td class="c">{{ item['date'] }}</td>
			</tr>
		{% endfor %}
	</table>
	<div class="separator"></div>
	{% for item in list %}
		<div id="ticket_{{ item['id'] }}" style="display:none;" class="tickets">
			<form action="{{ url('support/answer/'~item['id']~'/') }}" method="POST">
				<table class="table">
					<tr>
						<th>Текст запроса</th>
					</tr>
					<tr>
						<td class="c text-left">{{ item['text'] }}</td>
					</tr>
					{% if item['status'] == 0 %}<tr><th>Закрыт</th></tr>{% endif %}
					<tr>
						<td class="c">
							{% if item['status'] != 0 %}
								<textarea style="width: 99%" rows="10" name="text" title=""></textarea><br><input type="submit" value="Ответить">
							{% endif %}
						</td>
					</tr>
				</table>
			</form>
		</div>
		{% endfor %}
	{% else %}
		<tr>
			<th colspan="4">Нет запросов в техподдержку</th>
		</tr>
	</table>
	{% endif %}
	<br><br>

	<div id="newbutton" style="display:block;">
		<table class="table">
			<tr>
				<th><a href="javascript:;" onclick="ShowHiddenBlock('new');">Создать запрос</a></th>
			</tr>
		</table>
	</div>
	<div id="new" style="display:none;">
		<form action="{{ url('support/new/') }}" method="POST">
			<table class="table">
				<tr>
					<th colspan="2" width="50%">Новый запрос</th>
				</tr>
				<tr>
					<td class="c">Тема:</td>
					<td class="c"><input type="text" name="subject" title=""></td>
				</tr>
				<tr>
					<td class="c" colspan="2">Текст сообщения:</td>
				</tr>
				<tr>
					<td class="c p-a-0" colspan="2">
						<text-editor></text-editor>

						<input type="submit" value="Отправить">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>