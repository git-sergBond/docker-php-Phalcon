<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("categories", "Назад") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение категории
    </h1>
</div>

{{ content() }}

{{ form("categories/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldCategoryname" class="col-sm-2 control-label">Название категории</label>
    <div class="col-sm-10">
        {{ text_field("categoryName", "size" : 30, "class" : "form-control", "id" : "fieldCategoryname") }}
    </div>
</div>


{{ hidden_field("categoryId") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Изменить', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
