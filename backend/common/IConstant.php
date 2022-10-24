<?php

namespace common;

abstract class IConstant
{
    // <editor-fold desc="Коллекции">
    public const COLLECTION_GENDERS = 'genders';
    public const COLLECTION_IDENTITY_DOCUMENT_TYPES = 'identity-document-types';
    public const COLLECTION_CONTRACTOR_TYPES = 'contractor-types';
    public const COLLECTION_EMPLOYEE_JOB_TITLES = 'employee-job-titles';
    public const COLLECTION_FEEDING = 'feedings';
    public const COLLECTION_DISCOUNT_TYPE = 'discount-types';
    public const COLLECTION_DISCOUNT_ADVANCE_CAUSES = 'discount-advance-causes';
    public const COLLECTION_DISCOUNT_CARD_TYPES = 'discount-card-types';
    public const COLLECTION_ACCOUNT_STATUSES = 'account-statuses';
    public const COLLECTION_CONTRACTOR_STATUSES = 'contractor-statuses';
    public const COLLECTION_FILE_TEMPLATE_TYPES = 'file-template-types';
    public const COLLECTION_TYPE_NAVIGATION = 'type-navigation';
    public const COLLECTION_ORDER_TYPE = 'order-types';
    public const COLLECTION_CABIN_PLACE_TYPES = 'cabin-place-types';
    public const COLLECTION_PLACE_TYPES = 'place-types';
    public const COLLECTION_PAYMENT_STATUS_TYPES = 'payment-status-types';
    public const COLLECTION_ORDER_PAYMENT_STATUS = 'order-payment-status';
    public const COLLECTION_INSURANCE_TYPES = 'insurance-types';

    public const COLLECTION_SHIP_FIRST_LAST_SERVICES = 'ship-first-last-services';
    public const COLLECTION_SHIP_TYPES = 'ship-types';
    public const COLLECTION_SHIP_JOB_TITLES = 'ship-job-titles';
    public const COLLECTION_SHIP_STATUSES = 'ship-statuses';
    public const COLLECTION_SHIP_OWNER_TYPES = 'ship-owner-types';
    public const COLLECTION_SHIP_VIEW_TYPES = 'ship-view-types';
    // </editor-fold>

    // <editor-fold desc="Пол">
    public const CONTRACTOR_PHYSICAL_PERSON = 'CONTRACTOR_PHYSICAL_PERSON';
    public const CONTRACTOR_LEGAL_ENTITY = 'CONTRACTOR_LEGAL_ENTITY';
    public const CONTRACTOR_AGENT = 'CONTRACTOR_AGENT';
    public const CONTRACTOR_EMPLOYEE = 'CONTRACTOR_EMPLOYEE';
    // </editor-fold>

    // <editor-fold desc="Пол">
    public const GENDER_MAN = 'GENDER_MAN';
    public const GENDER_WOMEN = 'GENDER_WOMEN';
    // </editor-fold>

    // <editor-fold desc="Тип паспорта">
    public const IDENTITY_DOCUMENT_PASSPORT_RF = 'IDT_PASSPORT_RF';
    public const IDENTITY_DOCUMENT_PASSPORT_IA = 'IDT_PASSPORT_IA';
    public const IDENTITY_DOCUMENT_BIRTH_CERT = 'IDT_BIRTH_CERT';
    public const IDENTITY_DOCUMENT_PASSPORT_USSR = 'IDT_PASSPORT_USSR';
    public const IDENTITY_DOCUMENT_PASSPORT_SEA = 'IDT_PASSPORT_SEA';
    public const IDENTITY_DOCUMENT_PASSPORT_FP = 'IDT_PASSPORT_FP';
    public const IDENTITY_DOCUMENT_PASSPORT_SOLIDER = 'IDT_PASSPORT_SOLIDER';
    public const IDENTITY_DOCUMENT_PASSPORT_SIC = 'IDT_PASSPORT_SIC';
    public const IDENTITY_DOCUMENT_PASSPORT_TEMP = 'IDT_PASSPORT_TEMP';
    public const IDENTITY_DOCUMENT_MILITARY_ID = 'IDT_MILITARY_ID';
    public const IDENTITY_DOCUMENT_RESIDENT_CARD = 'IDT_RESIDENT_CARD';
    public const IDENTITY_DOCUMENT_MLS_CERT = 'IDT_MLS_CERT';
    public const IDENTITY_DOCUMENT_DIPLOMATIC_PASSPORT = 'IDT_DIPLOMATIC_PASSPORT';
    public const IDENTITY_DOCUMENT_SERVICE_PASSPORT = 'IDT_SERVICE_PASSPORT';
    public const IDENTITY_DOCUMENT_CERT_RETURN_CIS = 'IDT_CERT_RETURN_CIS';
    public const IDENTITY_DOCUMENT_CERT_LOST_PASSPORT = 'IDT_CERT_LOST_PASSPORT';
    // </editor-fold>

    // <editor-fold desc="Тип корабля">
    public const SHIP_TYPE_LARGE_FLEET = 'Корабль большого флота';
    public const SHIP_TYPE_SMALL_FLEET = 'Корабль малого флота';
    public const SHIP_TYPE_OTHER = 'Сторонний теплоход';
    // </editor-fold>

    // <editor-fold desc="Должности на корабле">
    public const SJT_MAID = 'SJT_0001';
    public const SJT_MINDER = 'SJT_0002';
    public const SJT_SENIOR_MINDER = 'SJT_0003';
    public const SJT_SAILOR = 'SJT_0004';
    public const SJT_SENIOR_SAILOR = 'SJT_0005';
    public const SJT_STEERING = 'SJT_0006';
    public const SJT_ASSISTANT_MECHANIC = 'SJT_0007';
    public const SJT_ASSISTANT_CAPITAN = 'SJT_0008';
    public const SJT_RADIO_OPERATOR = 'SJT_0009';
    public const SJT_ASSISTANT_MECHANIC_REFRIGERATION = 'SJT_0010';
    public const SJT_SENIOR_CONDUCTOR = 'SJT_0011';
    public const SJT_DISHWASHER = 'SJT_0012';
    public const SJT_COOK = 'SJT_0013';
    public const SJT_WAITER = 'SJT_0014';
    public const SJT_ELECTRICIAN = 'SJT_0015';
    // </editor-fold>

    // <editor-fold desc="Должности для сотрудников">
    public const EJT_SENIOR_MANAGER = 'EJT_0001';
    public const EJT_MANAGER = 'EJT_0002';
    public const EJT_CFO = 'EJT_0003';
    public const EJT_BOOKKEEPER = 'EJT_0004';
    public const EJT_CRUISE_DIRECTOR = 'EJT_0005';
    // </editor-fold>

    // <editor-fold desc="Питание">
    public const FEEDING_UNDEFINED = 'FEEDING_UNDEFINED';
    public const FEEDING_NOT = 'FEEDING_NOT';
    public const FEEDING_VARIANT_1 = 'FEEDING_VARIANT_1';
    public const FEEDING_VARIANT_2 = 'FEEDING_VARIANT_2';
    public const FEEDING_VARIANT_3 = 'FEEDING_VARIANT_3';
    public const FEEDING_VARIANT_4 = 'FEEDING_VARIANT_4';
    public const FEEDING_VARIANT_5 = 'FEEDING_VARIANT_5';
    public const FEEDING_VARIANT_6 = 'FEEDING_VARIANT_6';
//    const FEEDING_VARIANT_7 = "FEEDING_VARIANT_7";
//    const FEEDING_VARIANT_8 = "FEEDING_VARIANT_8";
    // </editor-fold>

    // <editor-fold desc="Первая или последняя услуга на корабле">
    public const SFLS_NOT = 'SFLS_NOT';
    public const SFLS_VARIANT_1 = 'SFLS_VARIANT_1';
    public const SFLS_VARIANT_2 = 'SFLS_VARIANT_2';
    public const SFLS_VARIANT_3 = 'SFLS_VARIANT_3';
    // </editor-fold>

    // <editor-fold desc="Уникальные условия для скидок ()">
    public const DISCOUNT_ADVANCE_CAUSE_TYPE_1 = 'DAC_TYPE_1';
    public const DISCOUNT_ADVANCE_CAUSE_TYPE_2 = 'DAC_TYPE_2';
    public const DISCOUNT_ADVANCE_CAUSE_TYPE_3 = 'DAC_TYPE_3';
    public const DISCOUNT_ADVANCE_CAUSE_TYPE_4 = 'DAC_TYPE_4';
    public const DISCOUNT_ADVANCE_CAUSE_TYPE_5 = 'DAC_TYPE_5';
    // </editor-fold>

    // <editor-fold desc="Типы скидок">
    public const DISCOUNT_TYPE_BASE = 'DISCOUNT_TYPE_BASE';
    public const DISCOUNT_TYPE_EARLY_RESERVE = 'DISCOUNT_TYPE_EARLY_RESERVE';
    public const DISCOUNT_TYPE_CLIENT = 'DISCOUNT_TYPE_CLIENT';
    public const DISCOUNT_TYPE_ONLINE = 'DISCOUNT_TYPE_ONLINE';
    // </editor-fold>

    // <editor-fold desc="Типы скидочных карт">
    public const DISCOUNT_CARD_TYPE_1 = 'DISCOUNT_CARD_TYPE_1';
    public const DISCOUNT_CARD_TYPE_2 = 'DISCOUNT_CARD_TYPE_2';
    public const DISCOUNT_CARD_TYPE_3 = 'DISCOUNT_CARD_TYPE_3';
    // </editor-fold>

    // <editor-fold desc="Типы скидочных карт">
    public const ACCOUNT_STATUS_ACTIVE = 'AS_ACTIVE';
    public const ACCOUNT_STATUS_BLOCKED = 'AS_BLOCKED';
    // </editor-fold>

    // <editor-fold desc="Типы скидочных карт">
    public const CONTRACTOR_STATUS_ACTIVE = 'AS_ACTIVE';
    public const CONTRACTOR_STATUS_BLOCKED = 'AS_BLOCKED';
    // </editor-fold>

    // <editor-fold desc="Тип шаблона документа">
    public const FILE_TEMPLATE_TYPE_1 = 'FT_TYPE_1';
    // </editor-fold>

    // <editor-fold desc="Типы навигации">
    public const TYPE_NAVIGATION_STOP = 'NAVIGATION_STOP';
    public const TYPE_NAVIGATION_RIDE = 'NAVIGATION_RIDE';
    // </editor-fold>

    // <editor-fold desc="Тип записи резервации">
    public const ORDER_TYPE_TEMP = 'OT_20220805100630';
    public const ORDER_TYPE_GENERAL = 'OT_20220805100701';
    // </editor-fold>

    // <editor-fold desc="Местоположение места в каюте">
    public const CABIN_PLACE_TYPE_TOP = 'CPT_TOP';
    public const CABIN_PLACE_TYPE_BOTTOM = 'CPT_BOTTOM';
    // </editor-fold>

    // <editor-fold desc="Статус каюты">
    public const CABIN_STATUS_BLOCKED = 'CABIN_STATUS_BLOCKED';
    public const CABIN_STATUS_RESERVED = 'CABIN_STATUS_RESERVED';
    public const CABIN_STATUS_AVAILABLE = 'CABIN_STATUS_AVAILABLE';
    // </editor-fold>

    // <editor-fold desc="Классы кают заказа">
    public const PLACE_TYPE_BASE = 'PLACE_TYPE_BASE';
    public const PLACE_TYPE_ADVANCE = 'PLACE_TYPE_ADVANCE';
    // </editor-fold>

    // <editor-fold desc="Статус оплаты заказа">
    public const PAYMENT_STATUS_NOT_PAYED = 'PAYMENT_STATUS_NOT_PAYED';
    public const PAYMENT_STATUS_PAYED = 'PAYMENT_STATUS_PAYED';
    public const PAYMENT_STATUS_PARTIALLY_PAYED = 'PAYMENT_STATUS_PARTIALLY_PAYED';
    public const PAYMENT_STATUS_OVER_PAYED = 'PAYMENT_STATUS_OVER_PAYED';
    // </editor-fold>

    // <editor-fold desc="Тип Payments">
    public const ORDER_PAYMENT_STATUS_CREATED = 'ORDER_PAYMENT_STATUS_CREATED';
    public const ORDER_PAYMENT_STATUS_INIT = 'ORDER_PAYMENT_STATUS_INIT';
    public const ORDER_PAYMENT_STATUS_DEPOSIT = 'ORDER_PAYMENT_STATUS_DEPOSIT';
    public const ORDER_PAYMENT_STATUS_CANCEL = 'ORDER_PAYMENT_STATUS_CANCEL';
    public const ORDER_PAYMENT_STATUS_DENIED = 'ORDER_PAYMENT_STATUS_DENIED';
    public const ORDER_PAYMENT_STATUS_RETURN = 'ORDER_PAYMENT_STATUS_RETURN';
    // </editor-fold>

    // <editor-fold desc="Тип страховки">
    public const INSURANCE_SINGLE = 'INSURANCE_SINGLE';
    public const INSURANCE_MULTIPLE = 'INSURANCE_MULTIPLE';
    // </editor-fold>
    // <editor-fold desc="Статус корабля">
    public const SHIP_STATUS_ACTIVE = 'SHIP_STATUS_ACTIVE';
    public const SHIP_STATUS_DISABLE = 'SHIP_STATUS_DISABLE';
    // </editor-fold>

    // <editor-fold desc="Корабль в собственности">
    public const SHIP_OWNER_SELF = 'SHIP_OWNER_SELF';
    public const SHIP_OWNER_OTHER = 'SHIP_OWNER_OTHER';
    // </editor-fold>

    // <editor-fold desc="Отображение кают на корабле">
    public const SHIP_VIEW_TICKET = 'SHIP_VIEW_TICKET';
    public const SHIP_VIEW_TABLE = 'SHIP_VIEW_TABLE';
    public const SHIP_VIEW_SCHEME = 'SHIP_VIEW_SCHEME';
    // </editor-fold>
}
