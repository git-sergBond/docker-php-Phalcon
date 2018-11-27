<div class="page-header">
    <h1>
        Задания
    </h1>
    <p>
        {{ link_to("tasksModer/new", "Создать задание") }}
    </p>
</div>

{{ content() }}

{{ form("tasksModer/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        {{ text_field("name", "size" : 50, "class" : "form-control", "id" : "fieldName") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">Пользователь</label>
    <div class="col-sm-10">
        <!--{{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}-->
        {{ select('userId', users, 'using':['userId', 'email'],'useEmpty':true,
        'emptyValue':null, 'emptyText':'', 'class':'form-control', 'id':'fieldUserid') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId', categories, 'using':['categoryId', 'categoryName'],"useEmpty":true,"emptyValue":null,
        'emptyText':'',"class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldStatus" class="col-sm-2 control-label">Статус</label>
    <div class="col-sm-10">
        {{ select_static('status', ['Поиск':'Поиск','Выполняется':'Выполняется','Завершено':'Завершено'],"useEmpty":true,"emptyValue":null,
        'emptyText':'',"class" : "form-control", "id" : "fieldStatus") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Фильтр', 'class': 'btn btn-default') }}
    </div>
</div>

</form>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
            <th>Название</th>
            <th>Пользователь</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Дата завершения</th>
            <th>Статус</th>
            <th>Цена</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for task in page.items %}
            <tr>
                <td>{{ task.getName() }}</td>
            <td>{{ link_to("userinfo/viewprofile/"~task.users.getUserId(),task.users.getEmail()) }}</td>
            <td>{{ task.categories.getCategoryName() }}</td>
            <td>{{ task.getDescription() }}</td>
            <td>{{ task.getAddress() }}</td>
            <td>{{ task.getDeadline() }}</td>
            <td>{{ task.getStatus() }}</td>
            <td>{{ task.getPrice() }}</td>

                <td>{{ link_to("tasksModer/edit/"~task.getTaskid(), "Изменить") }}</td>
                <td>{{ link_to("tasksModer/delete/"~task.getTaskid(), "Удалить") }}</td>
            </tr>
        {% endfor %}
        {% endif %}
        </tbody>
    </table>
</div>

{% if page.total_pages>1 %}
<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            {{ page.current~"/"~page.total_pages }}
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li>{{ link_to("tasksModer/index", "Первая") }}</li>
                <li>{{ link_to("tasksModer/index?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("tasksModer/index?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("tasks/index?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
{% endif %}