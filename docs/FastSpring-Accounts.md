FastSpring Accounts docs
Create an account

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create an account

Creates a new account object.

When you create an account, we recommend to include the customer's country. Some transactions may fail if you do not include it, as this field is required for specific payment methods.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Accounts",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "auth": []
    }
  ],
  "tags": [
    {
      "name": "Accounts",
      "description": "Create additional accounts and retrieve account-related information."
    }
  ],
  "paths": {
    "/accounts": {
      "post": {
        "summary": "Create an account",
        "tags": [
          "Accounts"
        ],
        "description": "Creates a new account object.\n\nWhen you create an account, we recommend to include the customer's country. Some transactions may fail if you do not include it, as this field is required for specific payment methods.\n",
        "operationId": "Createanaccount",
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateOneAccountRequest"
              }
            }
          },
          "required": true
        },
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CreateOneAccountResponse"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "CreateOneAccountRequest": {
        "type": "object",
        "properties": {
          "contact": {
            "type": "object",
            "required": [
              "first",
              "last",
              "email"
            ],
            "properties": {
              "first": {
                "type": "string",
                "description": "The first name of the designated account contact.",
                "example": "First"
              },
              "last": {
                "type": "string",
                "description": "The last name of the designated account contact.",
                "example": "Last"
              },
              "email": {
                "type": "string",
                "description": "The email address of the designated account contact.",
                "example": "first.last@domain.com"
              },
              "company": {
                "type": "string",
                "description": "The company of the designated account contact.",
                "example": "Company Name"
              },
              "phone": {
                "type": "string",
                "description": "The telephone number of the designated account contact.",
                "example": "555-555-5555"
              }
            }
          },
          "language": {
            "type": "string",
            "description": "Two-letter language code (<a href=\"https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes\">ISO 639</a>).",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "Two-letter country code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2\">ISO 3166-1 alpha-2</a>).",
            "example": "US"
          },
          "lookup": {
            "type": "object",
            "properties": {
              "custom": {
                "type": "string",
                "description": "A custom lookup key used to retrieve account details dynamically from a static string. The string length must be greater than or equal to 4.",
                "example": "customKey"
              }
            }
          }
        }
      },
      "CreateOneAccountResponse": {
        "type": "object",
        "properties": {
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "id": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.create"
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          }
        }
      }
    }
  }
}
```

List all accounts

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all accounts

Returns a list of your accounts.

If no parameters are sent, the operation will return an array of account IDs.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Accounts",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "auth": []
    }
  ],
  "tags": [
    {
      "name": "Accounts",
      "description": "Create additional accounts and retrieve account-related information."
    }
  ],
  "paths": {
    "/accounts": {
      "get": {
        "summary": "List all accounts",
        "tags": [
          "Accounts"
        ],
        "description": "Returns a list of your accounts.\n\nIf no parameters are sent, the operation will return an array of account IDs.\n",
        "operationId": "LookUpAccountsbyParameters",
        "deprecated": false,
        "parameters": [
          {
            "name": "email",
            "in": "query",
            "description": "Only return accounts with the given email address.",
            "schema": {
              "type": "string",
              "example": "first.last@domain.com"
            }
          },
          {
            "name": "custom",
            "in": "query",
            "description": "Only return accounts with the given custom lookup key.",
            "schema": {
              "type": "string",
              "example": "customKey"
            }
          },
          {
            "name": "global",
            "in": "query",
            "description": "Only return accounts with the given global lookup key.",
            "schema": {
              "type": "string",
              "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
            }
          },
          {
            "name": "orderId",
            "in": "query",
            "description": "Only return accounts with the given order ID.",
            "schema": {
              "type": "string",
              "example": "aAB0cdefGHiJkL0m8n8OpQ"
            }
          },
          {
            "name": "orderReference",
            "in": "query",
            "description": "Only return accounts with the given order reference.",
            "schema": {
              "type": "string",
              "example": "ABC230906-7581-30108"
            }
          },
          {
            "name": "subscriptionId",
            "in": "query",
            "description": "Only return accounts with the given subscription ID.",
            "schema": {
              "type": "string",
              "example": "84a1bCDEf86g_-HijK_LMN"
            }
          },
          {
            "name": "products",
            "in": "query",
            "description": "Only return accounts with the given product ID.",
            "schema": {
              "type": "string",
              "example": "product-testing"
            }
          },
          {
            "name": "subscriptions",
            "in": "query",
            "description": "Only return accounts with subscriptions in the corresponding state.",
            "schema": {
              "type": "string",
              "enum": [
                "active",
                "ended",
                "canceled",
                "started"
              ]
            }
          },
          {
            "name": "refunds",
            "in": "query",
            "description": "Only return accounts that include refunds. You can only pass the value 'true' for this parameter.",
            "schema": {
              "type": "string",
              "example": "true"
            }
          },
          {
            "name": "limit",
            "in": "query",
            "description": "A limit on the number of objects to be returned per page (default limit is 50).",
            "schema": {
              "type": "number",
              "example": 50
            }
          },
          {
            "name": "page",
            "in": "query",
            "description": "Specify which page of results should be returned.",
            "schema": {
              "type": "number",
              "example": 1
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllAccounts"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "GetAllAccounts": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.getall"
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          },
          "page": {
            "type": "integer",
            "description": "The page number of results returned.",
            "example": 1
          },
          "limit": {
            "type": "integer",
            "description": "The limit of how many objects were returned per page.",
            "example": 50
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results returned.",
            "example": 2
          },
          "total": {
            "type": "integer",
            "description": "The total number of accounts returned.",
            "example": 2
          },
          "accounts": {
            "type": "array",
            "description": "Array of account IDs.",
            "example": [
              "aAB0cdefGHiJkL0m8n8OpQ",
              "rST2uv1wXy279zABCd_E2f"
            ]
          }
        }
      }
    }
  }
}
```

Retrieve an account

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve an account

Returns the details of an account with the given `account_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Accounts",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "auth": []
    }
  ],
  "tags": [
    {
      "name": "Accounts",
      "description": "Create additional accounts and retrieve account-related information."
    }
  ],
  "paths": {
    "/accounts/{account_id}": {
      "get": {
        "summary": "Retrieve an account",
        "tags": [
          "Accounts"
        ],
        "description": "Returns the details of an account with the given `account_id`.",
        "operationId": "GetOneAccount",
        "deprecated": false,
        "parameters": [
          {
            "name": "account_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the account.",
            "schema": {
              "type": "string",
              "example": "abcDEFgHiJklM1N-3OP9q"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetOneAccount"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetOneAccountError400"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "GetOneAccount": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.get"
          },
          "contact": {
            "type": "object",
            "properties": {
              "first": {
                "type": "string",
                "description": "The first name of the designated account contact.",
                "example": "First"
              },
              "last": {
                "type": "string",
                "description": "The last name of the designated account contact.",
                "example": "Last"
              },
              "email": {
                "type": "string",
                "description": "The email address of the designated account contact.",
                "example": "first.last@domain.com"
              },
              "company": {
                "type": "string",
                "description": "The company of the designated account contact.",
                "example": "Company Name"
              },
              "phone": {
                "type": "string",
                "description": "The telephone number of the designated account contact.",
                "example": "555-555-5555"
              },
              "subscribed": {
                "type": "boolean",
                "description": "A boolean indicating whether an account includes a subscription or not.",
                "example": true
              }
            }
          },
          "address": {
            "type": "object",
            "properties": {
              "addressLine1": {
                "type": "string",
                "description": "Address line 1 (e.g., street, PO box, or company name).",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "description": "Address line 2 (e.g., apartment, suite, unit, or building).",
                "example": "Apt. 456"
              },
              "city": {
                "type": "string",
                "description": "City, district, suburb, town, or village.",
                "example": "Santa Barbara"
              },
              "region": {
                "type": "string",
                "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                "example": "US-CA"
              },
              "regionCustom": {
                "type": "string",
                "description": "Phone number",
                "example": "555-555-5555"
              },
              "postalCode": {
                "type": "string",
                "description": "ZIP or postal code.",
                "example": 93101
              },
              "company": {
                "type": "string",
                "description": "Company or account name.",
                "example": "Company Name"
              }
            }
          },
          "language": {
            "type": "string",
            "description": "Two-letter language code (<a href=\"https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes\">ISO 639</a>).",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "Two-letter country code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2\">ISO 3166-1 alpha-2</a>).",
            "example": "US"
          },
          "lookup": {
            "type": "object",
            "properties": {
              "global": {
                "type": "string",
                "description": "A global lookup key used to retrieve account details dynamically from a static string.",
                "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
              },
              "custom": {
                "type": "string",
                "description": "A custom lookup key used to retrieve account details dynamically from a static string.",
                "example": "customKey"
              }
            }
          },
          "url": {
            "type": "string",
            "description": "FastSpring account URL.",
            "example": "https://user.test.onfastspring.com/account"
          },
          "payment": {
            "type": "object",
            "properties": {
              "methods": {
                "type": "integer",
                "description": "Total of payment methods available.",
                "example": 1
              },
              "active": {
                "type": "integer",
                "description": "Total of active payment methods available.",
                "example": 1
              }
            }
          },
          "orders": {
            "type": "array",
            "description": "Array of orders placed by the associated account ID. Each order is represented by a unique order ID.",
            "example": [
              "aAB0cdefGHiJkL0m8n8OpQ",
              "rST2uv1wXy279zABCd_E2f"
            ]
          },
          "subscriptions": {
            "type": "array",
            "description": "Array of subscriptions added by the associated account ID. Each subscription is represented by a unique subscription ID.",
            "example": [
              "84a1bCDEf86g_-HijK_LMN"
            ]
          },
          "charges": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/RetrieveCharges"
            }
          },
          "subscribed": {
            "type": "boolean",
            "description": "A boolean indicating whether an account includes a subscription or not.",
            "example": true
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          },
          "taxExemptionData": {
            "type": "string",
            "description": "Tax exemption details.",
            "example": null
          }
        }
      },
      "RetrieveCharges": {
        "type": "object",
        "properties": {
          "currency": {
            "type": "string",
            "description": "Three-letter currency code (<a href=\"https://en.wikipedia.org/wiki/ISO_4217\">ISO 4217</a>).",
            "example": "USD"
          },
          "total": {
            "type": "string",
            "description": "Total after discounts and taxes.",
            "example": 100
          },
          "payoutCurrency": {
            "type": "string",
            "description": "Three-letter currency code (<a href=\"https://en.wikipedia.org/wiki/ISO_4217\">ISO 4217</a>).",
            "example": "USD"
          },
          "totalInPayoutCurrency": {
            "type": "integer",
            "description": "Total after discounts and taxes.",
            "example": 100
          },
          "status": {
            "type": "string",
            "description": "The status of the charge.",
            "enum": [
              "successful",
              "failed"
            ],
            "example": "successful"
          },
          "order": {
            "type": "string",
            "description": "The order ID associated with this charge.",
            "example": "aAB0cdefGHiJkL0m8n8OpQ"
          },
          "orderReference": {
            "type": "string",
            "description": "The order reference associated with this charge.",
            "example": "ABC230906-7581-30108"
          },
          "subscription": {
            "type": "string",
            "description": "The subscription ID associated with this charge.",
            "example": "84a1bCDEf86g_-HijK_LMN"
          },
          "timestamp": {
            "type": "integer",
            "format": "date-time",
            "description": "The timestamp when this charge happened (Unix).",
            "example": 1693959029538
          },
          "timestampValue": {
            "type": "integer",
            "format": "date-time",
            "description": "The timestamp when this charge happened (Unix).",
            "example": 1693959029538
          },
          "timestampInSeconds": {
            "type": "integer",
            "format": "date-time",
            "description": "The timestamp when this charge happened (Unix seconds).",
            "example": 1693959029
          },
          "timestampDisplay": {
            "type": "integer",
            "format": "date-time",
            "description": "The timestamp when this charge happened (MM/DD/YY).",
            "example": "8/2/24"
          },
          "timestampDisplayISO8601": {
            "type": "integer",
            "format": "date-time",
            "description": "The timestamp when this charge happened (<a href=\"https://www.iso.org/iso-8601-date-and-time-format.html\">ISO 8601</a>).",
            "example": "2024-08-02"
          }
        }
      },
      "GetOneAccountError400": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.get"
          },
          "result": {
            "type": "string",
            "description": "The outcome of the API request, indicating an error.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "account": {
                "type": "string",
                "description": "A message describing the error.",
                "example": "account id not found"
              }
            }
          }
        }
      }
    }
  }
}
```

Update an account

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update an account

Updates an account by setting the values of the parameters passed. Any parameters not provided are left unchanged.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Accounts",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "auth": []
    }
  ],
  "tags": [
    {
      "name": "Accounts",
      "description": "Create additional accounts and retrieve account-related information."
    }
  ],
  "paths": {
    "/accounts/{account_id}": {
      "post": {
        "summary": "Update an account",
        "tags": [
          "Accounts"
        ],
        "description": "Updates an account by setting the values of the parameters passed. Any parameters not provided are left unchanged.",
        "operationId": "Updateexistingaccount",
        "deprecated": false,
        "parameters": [
          {
            "name": "account_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the account.",
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateOneAccountRequest"
              }
            }
          },
          "required": true
        },
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/UpdateOneAccountResponse"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "UpdateOneAccountRequest": {
        "type": "object",
        "properties": {
          "contact": {
            "type": "object",
            "required": [
              "email"
            ],
            "properties": {
              "first": {
                "type": "string",
                "description": "The first name of the designated account contact.",
                "example": "First"
              },
              "last": {
                "type": "string",
                "description": "The last name of the designated account contact.",
                "example": "Last"
              },
              "email": {
                "type": "string",
                "description": "The email address of the designated account contact.",
                "example": "first.last@domain.com"
              },
              "company": {
                "type": "string",
                "description": "The company of the designated account contact.",
                "example": "Company Name"
              },
              "phone": {
                "type": "string",
                "description": "The telephone number of the designated account contact.",
                "example": "555-555-5555"
              }
            }
          },
          "language": {
            "type": "string",
            "description": "Two-letter language code (<a href=\"https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes\">ISO 639</a>).",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "Two-letter country code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2\">ISO 3166-1 alpha-2</a>).",
            "example": "US"
          },
          "lookup": {
            "type": "object",
            "properties": {
              "custom": {
                "type": "string",
                "description": "A custom lookup key used to retrieve account details dynamically from a static string. The string length must be greater than or equal to 4.",
                "example": "customKey"
              }
            }
          }
        }
      },
      "UpdateOneAccountResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.update"
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          },
          "accounts": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/RetrieveUpdateAccountResponse"
            }
          }
        }
      },
      "RetrieveUpdateAccountResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.update"
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          }
        }
      }
    }
  }
}
```

Retrieve authenticated account management URL

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve authenticated account management URL

Returns the URL of a customer's Account Management Portal.

To direct customers to land on the **Subscriptions** tab instead of the **Orders** tab, append <code>#/subscriptions</code> to the URL returned in the API response.       


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Accounts",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "auth": []
    }
  ],
  "tags": [
    {
      "name": "Accounts",
      "description": "Create additional accounts and retrieve account-related information."
    }
  ],
  "paths": {
    "/accounts/{account_id}/authenticate": {
      "get": {
        "summary": "Retrieve authenticated account management URL",
        "tags": [
          "Accounts"
        ],
        "description": "Returns the URL of a customer's Account Management Portal.\n\nTo direct customers to land on the **Subscriptions** tab instead of the **Orders** tab, append <code>#/subscriptions</code> to the URL returned in the API response.       \n",
        "operationId": "GetauthenticatedaccountmanagementURL",
        "deprecated": false,
        "parameters": [
          {
            "name": "account_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the account.",
            "schema": {
              "type": "string",
              "example": "abcDEFgHiJklM1N-3OP9q"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/AuthenticateAccount"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/AuthenticateAccountError"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "AuthenticateAccount": {
        "type": "object",
        "properties": {
          "accounts": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/RetrieveAuthenticateAccount"
            }
          }
        }
      },
      "RetrieveAuthenticateAccount": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.authenticate.get"
          },
          "result": {
            "type": "string",
            "description": "Indicates that the request has succeeded or failed.",
            "example": "success"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "The timestamp of when the authenticated URL expires (<a href=\"https://www.iso.org/iso-8601-date-and-time-format.html\">ISO 8601</a>).",
            "example": "2024-09-17T15:15:35.366"
          },
          "url": {
            "type": "string",
            "description": "The URL of a customer's Account Management Portal.",
            "example": "https://company.onfastspring.com/account/50HsQS1-QcOR3dzEF_rm3w/ydYUZOVrQ24"
          }
        }
      },
      "AuthenticateAccountError": {
        "type": "object",
        "properties": {
          "accounts": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/AuthenticateAccountError400"
            }
          }
        }
      },
      "AuthenticateAccountError400": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "account.authenticate.get"
          },
          "account": {
            "type": "string",
            "description": "The unique account identifier.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "result": {
            "type": "string",
            "description": "The outcome of the API request, indicating an error.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "account": {
                "type": "string",
                "description": "A message describing the error.",
                "example": "Not found"
              }
            }
          }
        }
      }
    }
  }
}
```