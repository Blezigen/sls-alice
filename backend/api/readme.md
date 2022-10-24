# Заголовки
<table>
    <thead>
        <tr>
            <td><b>Ключ</b></td>
            <td><b>Описание</b>
            </td><td><b>Возможные значения</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>X-Api-Version</b></td>
            <td>Позволяет определить версию api на которую отправляются запросы</td>
            <td><b>1.0</b></td>
        </tr>
        <tr>
            <td><b>X-Debug</b></td>
            <td>Позволяет вывести дополнительную информацию по ошибке<br> Пример: <br><code>?debug=true</code><br><code>X-Debug:true</code></td>
            <td>
                <b>true</b> - увеличивает кол-во информации при выводе ошибки<br>
                <b>query</b> - позволяет вывести основной sql запрос, который используется на странице 
            </td>
        </tr>
    </tbody>
</table>

# Ошибки
## Системная ошибка (400, 500)
```json
{
    "errors": {
        "message": "Общее описание ошибки"
    },
    "debug": {}
}
```
## Не авторизован (401)
```json
{
    "errors": {
        "message": "Не авторизован"
    },
    "debug": {}
}
```

## Ошибка валидации (проверки правильности ввода) (422)
```json
{
    "errors": {
        "message": "Ошибка валидации",
        "messages": {
            "{field_name_1}": ["validation_error_1", "validation_error_2"],
            "{field_name_2}": ["validation_error_1", "validation_error_2"]
        }
    },
    "debug": {}
}
```

# Правила фильтрации (FQL)
## Правила использования операторов
<table class="table table-striped">
    <tr>
        <td><b>Оператор</b></td>
        <td><b>Описание</b></td>
        <td><b>Пример</b></td>
    </tr>
<tbody>
    <tr>
        <td><b>EQ(p1)</b></td>
        <td>Полное совпадение</td>
        <td><code>EQ(1)</code> <br> <code>EQ(text)</code></td>
    </tr>
    <tr>
        <td><b>NEQ(p1)</b></td>
        <td>Не должно совпадать</td>
        <td><code>NEQ(1)</code> <br> <code>NEQ(text)</code></td>
    </tr>
    <tr>
        <td><b>EQN</b></td>
        <td>Значение должно быть <code>NULL</code></td>
        <td><code>EQN</code></td>
    </tr>
    <tr>
        <td><b>NEQN</b></td>
        <td>Значение не должно быть <code>NULL</code></td>
        <td><code>NEQN</code></td>
    </tr>
    <tr>
        <td><b>GEQ(p1)</b></td>
        <td>Значение должно быть больше или равно</td>
        <td><code>GEQ(1)</code> <br> <code>GEQ(1.002)</code></td>
    </tr>
    <tr>
        <td><b>LEQ(p1)</b></td>
        <td>Значение должно быть меньше или равно</td>
        <td><code>LEQ(1)</code> <br> <code>LEQ(1.002)</code></td>
    </tr>
    <tr>
        <td><b>GE(p1)</b></td>
        <td>Значение должно быть больше</td>
        <td><code>GE(1)</code> <br> <code>GE(1.002)</code></td>
    </tr>
    <tr>
        <td><b>LE(p1)</b></td>
        <td>Значение должно быть меньше</td>
        <td><code>LE(1)</code> <br> <code>LE(1.002)</code></td>
    </tr>
    <tr>
        <td><b>LIKE(p1)</b></td>
        <td>Частичное совпадение <code>%p1%</code></td>
        <td><code>LIKE(text)</code></td>
    </tr>
    <tr>
        <td><b>BTW(p1,p2)</b></td>
        <td>Значение не должно быть меньше</td>
        <td>
            <code>BTW(100,200)</code> <br> 
            <code>BTW(1621258240,1621258240)</code> <br> 
            <code>BTW(DATE(2020-05-15 12:59:59),DATE(21.06.2021 21:06))</code> <br> 
            <code>BTW(DATE(8912437895328),DATE(21.06.2021 21:06))</code> <br> 
            <code>BTW(DATE(2020-05-15),DATE(21.06.2021))</code> <br> 
        </td>
    </tr>
    <tr>
        <td><b>IN(p1, p2,..., pN)</b></td>
        <td>Одно из значений</td>
        <td>
            <code>IN(NULL,1,2,3)</code>
            <code>IN(100,200,300,400)</code>
        </td>
    </tr>
    <tr>
        <td><b>ADDITION(p1, p2)</b></td>
        <td>
            Получить только те элементы множества, которые включены в указанное множество
        </td>
        <td>
            <code>ADDITION(100,200)</code>
            <code>ADDITION(2022-01-01,2022-12-31)</code>
        </td>
    </tr>
    <tr>
        <td><b>BYPERIOD(p1)</b></td>
        <td>
            Добавить фильтрацию по id периода
        </td>
        <td>
            <code>BYPERIOD(1)</code>
        </td>
    </tr>
</tbody>
</table>

<br>

## Правила использования преобразования параметров
<table class="table table-striped">
    <tr>
        <td><b>Преобразователь</b></td>
        <td><b>Описание</b></td>
        <td><b>Пример</b></td>
    </tr>
    <tr>
        <td><b>DATE(p1)</b></td>
        <td>Преобразование в дату</td>
        <td><code>DATE(1123123123)</code> <br> <code>DATE(2020-06-15)</code> <br> <code>DATE(21.06.1996)</code></td>
    </tr>
    <tr>
        <td><b>INT(p1)</b></td>
        <td>Преобразование в целое</td>
        <td><code>INT(1)</code> <br> <code>INT(text)</code></td>
    </tr>
    <tr>
        <td><b>FLOAT(p1)</b></td>
        <td>Преобразование в вещественное</td>
        <td><code>FLOAT(1)</code> <br> <code>FLOAT(1.002)</code></td>
    </tr>
</table>

# Правила поля extend
Для выгрузки подчинённой записи вместе с основной, необходимо в запросе указать <code>?extend={имя_сущности}</code>. 
Таким образом можно выгружать сколь угодно много, например указав параметр через точку <code>?extend=user.accounts</code>, 
то в таком случае выгружается: <code>user</code> и <code>user.accounts</code>. Учитывайте что таким образом выгружаются только ограниченное по кол-ву записи, для фильтрации такой способ не подходит.

<br><br>

