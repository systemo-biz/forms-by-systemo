Forms-for-WP-by-CasePress
=========================

Forms for WP by CasePress


# Examples
## Example #1 - Simple form

[form-cp method="GET"]

[input-cp type=text label=text name="text"]

[input-cp type=submit value="Отправить" name="submit"]

[/form-cp]


## Example #2 - form for landing page

[form-cp method="post" name_form="Сайт под ключ - заявка" style="width: 300px; padding: 11px;background-color: darkcyan;"]

[input-cp type=text name="name" placeholder="Имя" meta="Имя"]

[input-cp type=text name="tel" placeholder="Телефон" meta="Телефон"]

[input-cp type=email name="email" placeholder="Электронная почта" meta="Электронная почта"]

[textarea-cp placeholder=Комментарий name="comment" meta="Комментарий"]

[input-cp type=submit class="btn btn-success" value="Отправить" name="submit"]

[/form-cp]
