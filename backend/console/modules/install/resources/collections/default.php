<?php

return [
    \common\IConstant::COLLECTION_ORDER_TYPE => [
        \common\IConstant::ORDER_TYPE_TEMP => 'Временная бронь',
        \common\IConstant::ORDER_TYPE_GENERAL => 'Заказ',
    ],

    \common\IConstant::COLLECTION_CONTRACTOR_STATUSES => [
        \common\IConstant::CONTRACTOR_STATUS_ACTIVE => 'Активен',
        \common\IConstant::CONTRACTOR_STATUS_BLOCKED => 'Заблокирован',
    ],

    \common\IConstant::COLLECTION_ACCOUNT_STATUSES => [
        \common\IConstant::ACCOUNT_STATUS_ACTIVE => 'Активен',
        \common\IConstant::ACCOUNT_STATUS_BLOCKED => 'Заблокирован',
    ],

    \common\IConstant::COLLECTION_GENDERS => [
        \common\IConstant::GENDER_MAN => 'Мужской',
        \common\IConstant::GENDER_WOMEN => 'Женский',
    ],

    \common\IConstant::COLLECTION_IDENTITY_DOCUMENT_TYPES => [
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_RF => 'Паспорт гражданина РФ',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_IA => 'Паспорт иностранного гражданина',
        \common\IConstant::IDENTITY_DOCUMENT_BIRTH_CERT => 'Свидетельство о рождении',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_USSR => 'Паспорт гражданина СССР',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_SEA => 'Паспорт моряка',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_FP => 'Общегражданский заграничный паспорт РФ',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_SOLIDER => 'Удостоверение личности военнослужащего',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_SIC => 'Удостоверение личности без гражданства',
        \common\IConstant::IDENTITY_DOCUMENT_PASSPORT_TEMP => 'Временное удостоверение выдаваемое ОВД',
        \common\IConstant::IDENTITY_DOCUMENT_MILITARY_ID => 'Военный билет военнослужащего срочной службы',
        \common\IConstant::IDENTITY_DOCUMENT_RESIDENT_CARD => 'Вид на жительство',
        \common\IConstant::IDENTITY_DOCUMENT_MLS_CERT => 'Справка об освобождении из МЛС',
        \common\IConstant::IDENTITY_DOCUMENT_DIPLOMATIC_PASSPORT => 'Паспорт дипломатический',
        \common\IConstant::IDENTITY_DOCUMENT_SERVICE_PASSPORT => 'Паспорт служебный (кроме паспорта моряка и дипломатического)',
        \common\IConstant::IDENTITY_DOCUMENT_CERT_RETURN_CIS => 'Свидетельство о возвращении из стран СНГ',
        \common\IConstant::IDENTITY_DOCUMENT_CERT_LOST_PASSPORT => 'Справка об утере паспорта',
    ],

    \common\IConstant::COLLECTION_CONTRACTOR_TYPES => [
        \common\IConstant::CONTRACTOR_PHYSICAL_PERSON => 'Физики',
        \common\IConstant::CONTRACTOR_LEGAL_ENTITY => 'Юрики',
        \common\IConstant::CONTRACTOR_AGENT => 'Агенты',
        \common\IConstant::CONTRACTOR_EMPLOYEE => 'Сотрудники',
    ],
    \common\IConstant::COLLECTION_CABIN_PLACE_TYPES => [
        \common\IConstant::CABIN_PLACE_TYPE_TOP => 'Верхнее',
        \common\IConstant::CABIN_PLACE_TYPE_BOTTOM => 'Нижнее',
    ],

    \common\IConstant::COLLECTION_SHIP_TYPES => [
        \common\IConstant::SHIP_TYPE_LARGE_FLEET => 'Корабль большого флота',
        \common\IConstant::SHIP_TYPE_SMALL_FLEET => 'Корабль малого флота',
        \common\IConstant::SHIP_TYPE_OTHER => 'Сторонний теплоход',
    ],

    \common\IConstant::COLLECTION_SHIP_JOB_TITLES => [
        \common\IConstant::SJT_MAID => 'Горничная,',
        \common\IConstant::SJT_MINDER => 'Моторист,',
        \common\IConstant::SJT_SENIOR_MINDER => 'Старший моторист,',
        \common\IConstant::SJT_SAILOR => 'Матрос,',
        \common\IConstant::SJT_SENIOR_SAILOR => 'Старший матрос ,',
        \common\IConstant::SJT_STEERING => 'Рулевой,',
        \common\IConstant::SJT_ASSISTANT_MECHANIC => 'Помощник механика,',
        \common\IConstant::SJT_ASSISTANT_CAPITAN => 'Помощник капитана,',
        \common\IConstant::SJT_RADIO_OPERATOR => 'Радиооператор,',
        \common\IConstant::SJT_ASSISTANT_MECHANIC_REFRIGERATION => 'Помощник механика по рефрижераторной установке,',
        \common\IConstant::SJT_SENIOR_CONDUCTOR => 'Старший проводник,',
        \common\IConstant::SJT_DISHWASHER => 'Мойщик посуды,',
        \common\IConstant::SJT_COOK => 'Повар,',
        \common\IConstant::SJT_WAITER => 'Официант,',
        \common\IConstant::SJT_ELECTRICIAN => 'Электромеханик,',
    ],

    \common\IConstant::COLLECTION_EMPLOYEE_JOB_TITLES => [
        \common\IConstant::EJT_SENIOR_MANAGER => 'Руководитель менеджеров',
        \common\IConstant::EJT_MANAGER => 'Менеджер',
        \common\IConstant::EJT_CFO => 'Финансовый директор',
        \common\IConstant::EJT_BOOKKEEPER => 'Бухгалтер',
        \common\IConstant::EJT_CRUISE_DIRECTOR => 'Директор круиза',
    ],

    \common\IConstant::COLLECTION_FEEDING => [
        \common\IConstant::FEEDING_UNDEFINED => 'Нет',
        \common\IConstant::FEEDING_NOT => 'Без питания',
        \common\IConstant::FEEDING_VARIANT_1 => '3-х разовое',
        \common\IConstant::FEEDING_VARIANT_2 => 'Завтрак',
        \common\IConstant::FEEDING_VARIANT_3 => 'Обед',
        \common\IConstant::FEEDING_VARIANT_4 => 'Ужин',
        \common\IConstant::FEEDING_VARIANT_5 => 'Завтрак + обед',
        \common\IConstant::FEEDING_VARIANT_6 => 'Обед + ужин',
    ],

    \common\IConstant::COLLECTION_SHIP_FIRST_LAST_SERVICES => [
        \common\IConstant::SFLS_NOT => 'Нет',
        \common\IConstant::SFLS_VARIANT_1 => 'Завтрак',
        \common\IConstant::SFLS_VARIANT_2 => 'Обед',
        \common\IConstant::SFLS_VARIANT_3 => 'Ужин',
    ],

    \common\IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => [
        \common\IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_1 => 'Оплата онлайн',
        // Действует на любые 3 заказа, от одного контрагента
        \common\IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_2 => 'Проверка на пенсионера',
        // Система смотрит на переменные возраста для пенсионера
        \common\IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_3 => 'Проверка на ребёнка',
        // Система смотрит на переменные возраста для ребёнка
        \common\IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_4 => 'День рождения',
        // Система смотрит на документ, который подтверждает день рождения.
        \common\IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_5 => 'Раннее бронирование',
        // Смотрит на параметры скидки, отображается в случае если соблюдены правила.
    ],

    \common\IConstant::COLLECTION_DISCOUNT_TYPE => [
        \common\IConstant::DISCOUNT_TYPE_BASE => 'Базовая',
        \common\IConstant::DISCOUNT_TYPE_EARLY_RESERVE => 'Раннее бронирование',
        \common\IConstant::DISCOUNT_TYPE_CLIENT => 'Постоянному клиенту',
        \common\IConstant::DISCOUNT_TYPE_ONLINE => 'Онлайн бронирование',
    ],

    \common\IConstant::COLLECTION_DISCOUNT_CARD_TYPES => [
        \common\IConstant::DISCOUNT_CARD_TYPE_1 => 'Золотая (15%)',
        \common\IConstant::DISCOUNT_CARD_TYPE_2 => 'Стандартная (7%)',
        \common\IConstant::DISCOUNT_CARD_TYPE_3 => 'Новичок (3%)',
    ],

    \common\IConstant::COLLECTION_FILE_TEMPLATE_TYPES => [
        \common\IConstant::FILE_TEMPLATE_TYPE_1 => 'Ваучер туриста',
    ],

    \common\IConstant::COLLECTION_PLACE_TYPES => [
        \common\IConstant::PLACE_TYPE_BASE => 'Основное место',
        \common\IConstant::PLACE_TYPE_ADVANCE => 'Дополнительное место',
    ],

    \common\IConstant::COLLECTION_TYPE_NAVIGATION => [
        \common\IConstant::TYPE_NAVIGATION_STOP => 'Стоянка',
        \common\IConstant::TYPE_NAVIGATION_RIDE => 'Прогулка',
    ],

    \common\IConstant::COLLECTION_PAYMENT_STATUS_TYPES => [
        \common\IConstant::PAYMENT_STATUS_NOT_PAYED => 'Неоплачено',
        \common\IConstant::PAYMENT_STATUS_PAYED => 'Оплачено',
        \common\IConstant::PAYMENT_STATUS_PARTIALLY_PAYED => 'Оплачено частично',
        \common\IConstant::PAYMENT_STATUS_OVER_PAYED => 'Переплата',
    ],

    \common\IConstant::COLLECTION_ORDER_PAYMENT_STATUS => [
        \common\IConstant::ORDER_PAYMENT_STATUS_CREATED => 'Создан',
        \common\IConstant::ORDER_PAYMENT_STATUS_INIT => 'Инициирован',
        \common\IConstant::ORDER_PAYMENT_STATUS_DEPOSIT => 'Поступила оплата',
        \common\IConstant::ORDER_PAYMENT_STATUS_CANCEL => 'Отмена авторизации платежа',
        \common\IConstant::ORDER_PAYMENT_STATUS_DENIED => 'Отказ в авторизации платежа',
        \common\IConstant::ORDER_PAYMENT_STATUS_RETURN => 'Возврат средств',
    ],

    \common\IConstant::COLLECTION_INSURANCE_TYPES => [
        \common\IConstant::INSURANCE_SINGLE => 'Персональная',
        \common\IConstant::INSURANCE_MULTIPLE => 'Общая',
    ],

    \common\IConstant::COLLECTION_SHIP_STATUSES => [
        \common\IConstant::SHIP_STATUS_ACTIVE => 'Активен',
        \common\IConstant::SHIP_STATUS_DISABLE => 'Не активен',
    ],
    \common\IConstant::COLLECTION_SHIP_OWNER_TYPES => [
        \common\IConstant::SHIP_OWNER_SELF => 'Собственный',
        \common\IConstant::SHIP_OWNER_OTHER => 'Чужой',
    ],
    \common\IConstant::COLLECTION_SHIP_VIEW_TYPES => [
        \common\IConstant::SHIP_VIEW_TICKET => 'Заявка',
        \common\IConstant::SHIP_VIEW_TABLE => 'Таблица',
        \common\IConstant::SHIP_VIEW_SCHEME => 'Схема',
    ],
];
