<?php

//*************************************
//Main Configuration

define("HOST_NAME", $_SERVER['SERVER_NAME']);
define("DOMAIN", "https://" . HOST_NAME);
define("APP_NAME", "Canada Post Web Client");
define("APP_URL", DOMAIN . "/canada_post"); 
define("APP_PATH", __DIR__);
define("COMPANY_NAME", "Your Company Name");
define("DEFAULT_LOCATION_ID", "1"); 
define("ORDER_CONTACT", "contact@" . HOST_NAME);


//************************************
//DB Connection

define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASSWORD", "");
define("DB_HOST", "localhost");
define("DB_CHARSET", "utf8");
define("DB_COLLATE", "");


//*************************************
// Canada Post Options
define("CP_LOCALE", "EN");
define("CP_COUNTRY_CODE", "CA");
define("CP_PRINT_OUTPUT_FORMAT", "4x6");


//************************************
//Canada Post Credentials

define("CP_CUSTOMER_NUMBER", ""); // Your Canada Post Customer Number
define("CP_ACCOUNT_NUMBER", ""); // Your Canada Post Account Number
define("CP_AGREEMENT_NUMBER", ""); //Your Canada Post Agreement Number
define("CP_CERT", "./cert/cacert.pem"); // Canada Post Digital Certificate
define("CP_PAID_BY_CUSTOMER", CP_ACCOUNT_NUMBER);


define("CP_USERNAME", ""); // Your Canada Post API Key
define("CP_PASS", ""); //Your Canada Post API Password
define("CP_PAYMENT_METHOD", "Account"); //Payment Method
define("CP_HOSTNAME", "ct.soa-gw.canadapost.ca");


//*************************************
// Canada Post SOAP URIs

define("CP_WS_SECURITY_TOKEN", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
define("CP_ESTIMATING_URL", "https://" . CP_HOSTNAME . "/rs/soap/rating/v3");
define("CP_SHIPMENT_URL", "https://" . CP_HOSTNAME . "/rs/soap/shipment/v8"); 
define("CP_ARTIFACT_URL", "https://" . CP_HOSTNAME . "/rs/soap/artifact");
define("CP_MANIFEST_URL", "https://" . CP_HOSTNAME . "/rs/soap/manifest/v8");
define("CP_AUTHRETURN_URL", "https://" . CP_HOSTNAME . "/rs/soap/authreturn/v2");
define("CP_PICKUP_AVAILABILITY_URL", "https://" . CP_HOSTNAME . "/ad/soap/pickup/availability");
define("CP_PICKUP_REQUEST_URL", "https://" . CP_HOSTNAME . "/enab/soap/pickuprequest");