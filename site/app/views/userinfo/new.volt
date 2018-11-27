<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("userinfo", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создать информацию о пользователе
    </h1>
</div>

{{ content() }}

{{ form("userinfo/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldFirstname" class="col-sm-2 control-label">Имя</label>
    <div class="col-sm-10">
        {{ text_field("firstname", "size" : 30, "class" : "form-control", "id" : "fieldFirstname") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPatronymic" class="col-sm-2 control-label">Отчество</label>
    <div class="col-sm-10">
        {{ text_field("patronymic", "size" : 30, "class" : "form-control", "id" : "fieldPatronymic") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldLastname" class="col-sm-2 control-label">Фамилия</label>
    <div class="col-sm-10">
        {{ text_field("lastname", "size" : 30, "class" : "form-control", "id" : "fieldLastname") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldBirthday" class="col-sm-2 control-label">Дата рождения</label>
    <div class="col-sm-10">
        {{ text_field("birthday", "type" : "date", "class" : "form-control", "id" : "fieldBirthday") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Пол</label>
    <div class="col-sm-10">
        {{ text_field("male", "type" : "numeric", "class" : "form-control", "id" : "fieldMale") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAddress" class="col-sm-2 control-label">Адресс</label>
    <div class="col-sm-10">
        {{ text_field("address", "size" : 30, "class" : "form-control", "id" : "fieldAddress") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAbout" class="col-sm-2 control-label">О себе</label>
    <div class="col-sm-10">
        {{ text_area("about", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldAbout") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldExecutor" class="col-sm-2 control-label">Исполнитель</label>
    <div class="col-sm-10">
        {{ text_field("executor", "type" : "numeric", "class" : "form-control", "id" : "fieldExecutor") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Добавить', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
