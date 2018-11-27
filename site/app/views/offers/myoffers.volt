<div class="page-header">
    <h1>
        Ваши предложения
    </h1>
    <p> {{ link_to("tasks/new", "Создать задание") }}</p>
    <p> {{ link_to("tasks/mytasks/"~userId, "Мои задания") }}</p>
    <p>  {{ link_to("offers/myoffers/"~userId, "Мои предложения") }}</p>
    <p>  {{ link_to("tasks/doingtasks/"~userId, "Мне выполняют задания") }}</p>
    <p>  {{ link_to("tasks/workingtasks/"~userId, "Мои выполняемые задания") }}</p>
</div>

{{ content() }}

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Наименование работ</th>
                <th>Описание работ</th>
            <th>Описание предложения</th>
            <th>Сроки</th>
            <th>Стоимость</th>
                <th colspan="2">Действия</th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for offers in page.items %}

            <tr>
                <td>{{ link_to("auctions/viewing/"~offers.getAuctionId(),offers.auctions.tasks.getName()) }}</td>
                <td>{{ offers.auctions.tasks.getDescription() }}</td>
            <td>{{ offers.getDescription() }}</td>
            <td>{{ offers.getDeadline() }}</td>
            <td>{{ offers.getPrice() }}</td>

                <td>{{ link_to("offers/editing/"~offers.getOfferId(), "Редактировать") }}</td>
                <td>{{ link_to("offers/deleting/"~offers.getOfferId(), "Удалить") }}</td>
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
                <li>{{ link_to("offers/myoffers/"~userId, "Первая") }}</li>
                <li>{{ link_to("offers/myoffers/"~userId~"?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("offers/myoffers/"~userId~"?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("offers/myoffers/"~userId~"?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
{% endif %}