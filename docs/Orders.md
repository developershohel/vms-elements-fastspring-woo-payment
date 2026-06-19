Orders

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Orders

The /orders endpoint retrieves information about specific order events, or all orders within specified criteria.\
You can apply the following parameters to return associated orders:

* Order ID
* Date range
* Product path

You can apply multiple parameters to a single request. The API will return paginated results with up to 50 records per page.

> 👍 To limit page results, specify a custom limit to the number of records per page. Include the desired number per page in your request.

The following request parameters are available when calling the /orders endpoint

<Table align={["left","left","left"]}>
  <thead>
    <tr>
      <th>
        Parameter
      </th>

      <th>
        Values
      </th>

      <th>
        Description
      </th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td>
        begin
      </td>

      <td>
        yyyy-mm-dd
      </td>

      <td>
        filters results to include transactions after the specified begin date (must be at least one day before the specified end date)
      </td>
    </tr>

    <tr>
      <td>
        days
      </td>

      <td>
        integer
      </td>

      <td>
        filters results to include transactions within a certain time frame
      </td>
    </tr>

    <tr>
      <td>
        end
      </td>

      <td>
        yyyy-mm-dd
      </td>

      <td>
        filters results to include transactions before the specified end date
      </td>
    </tr>

    <tr>
      <td>
        limit
      </td>

      <td>
        integer
      </td>

      <td>
        integer limits the number of order records returned per page (default is 50 records)
      </td>
    </tr>

    <tr>
      <td>
        page
      </td>

      <td>
        integer
      </td>

      <td>
        specifies page number of results to be returned; used together with limit to control pagination
      </td>
    </tr>

    <tr>
      <td>
        products
      </td>

      <td>
        string
      </td>

      <td>
        filters results to include only transactions involving the specified product ID(s) / product path(s)
      </td>
    </tr>

    <tr>
      <td>
        rebill
      </td>

      <td>
        true/false
      </td>

      <td>
        filters results to include only subscription rebill transactions or exclude all subscription rebill transactions
      </td>
    </tr>

    <tr>
      <td>
        returns
      </td>

      <td>
        true/false
      </td>

      <td>
        filters results to include orders with or without returns; response includes returns array with return IDs where applicable
      </td>
    </tr>

    <tr>
      <td>
        scope
      </td>

      <td>
        all,live,test
      </td>

      <td>
        filters results to include live transactions, test transactions, or both
      </td>
    </tr>

    <tr>
      <td>
        status
      </td>

      <td>
        completed, cancelled, failed
      </td>

      <td>
        filters results by transaction status; "completed" = successful orders, "canceled" = declined orders, "failed" = transaction failed to complete (very uncommon)
      </td>
    </tr>
  </tbody>
</Table>

List all orders

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all orders

Returns a list of your orders by order ID.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Orders",
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
      "name": "Orders",
      "description": "List, retrieve, and update orders.     \n"
    }
  ],
  "paths": {
    "/orders": {
      "get": {
        "summary": "List all orders",
        "tags": [
          "Orders"
        ],
        "description": "Returns a list of your orders by order ID.",
        "operationId": "Getordersbyproductpath",
        "deprecated": false,
        "parameters": [
          {
            "name": "begin",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include transactions after the specified begin date (in YYYY-MM-DD format). Must be at least one day before the specified end date.",
            "example": "2025-01-01"
          },
          {
            "name": "days",
            "in": "query",
            "required": false,
            "schema": {
              "type": "integer",
              "format": "int32"
            },
            "description": "Filters results to include transactions within a certain time frame.",
            "example": 30
          },
          {
            "name": "end",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include transactions before the specified end date (in YYYY-MM-DD format).",
            "example": "2025-01-31"
          },
          {
            "name": "limit",
            "in": "query",
            "required": false,
            "schema": {
              "type": "integer",
              "format": "int32",
              "default": 50
            },
            "description": "Limits the number of order records returned per page (default is 50 records).",
            "example": 50
          },
          {
            "name": "page",
            "in": "query",
            "required": false,
            "schema": {
              "type": "integer",
              "format": "int32",
              "default": 1
            },
            "description": "Specifies the page number of results to be returned; used together with limit to control pagination.",
            "example": 1
          },
          {
            "name": "products",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string"
            },
            "description": "Filters results to include only transactions involving the specified product path(s).",
            "example": "furious-falcon"
          },
          {
            "name": "rebill",
            "in": "query",
            "required": false,
            "schema": {
              "type": "boolean"
            },
            "description": "Filters results to include or exclude subscription rebill transactions.\n\nIf `true`, the results will include subscription rebill transactions.\n\nIf `false`, the results will exclude subscription rebill transactions.\n",
            "example": false
          },
          {
            "name": "returns",
            "in": "query",
            "required": false,
            "schema": {
              "type": "boolean"
            },
            "description": "Filters results to include orders with or without returns; response includes returns array with return IDs where applicable.",
            "example": true
          },
          {
            "name": "scope",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "enum": [
                "all",
                "live",
                "test"
              ]
            },
            "description": "Filters results to include live transactions, test transactions, or both.",
            "example": "test"
          },
          {
            "name": "status",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "enum": [
                "completed",
                "canceled",
                "failed"
              ]
            },
            "description": "Filters results by transaction status.\n\n`completed` = successful orders\n\n`canceled` = declined orders\n\n`failed` = transaction failed to complete\n",
            "example": "completed"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllOrders"
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
      "GetAllOrders": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "order.getall"
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
            "description": "The total number of order IDs returned.",
            "example": 2
          },
          "orders": {
            "type": "array",
            "description": "Array of order IDs.",
            "example": [
              "abcDEFgHiJklM1N-3OP9q",
              "rST2uv1wXy279zABCd_E2f"
            ]
          }
        }
      }
    }
  }
}
```

Retrieve an order

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve an order

Retrieves the details of an order with the given `order_id`.

If you specify multiple order IDs in the request, the response will return an array with each order object.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Orders",
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
      "name": "Orders",
      "description": "List, retrieve, and update orders.     \n"
    }
  ],
  "paths": {
    "/orders/{order_id}": {
      "get": {
        "summary": "Retrieve an order",
        "tags": [
          "Orders"
        ],
        "description": "Retrieves the details of an order with the given `order_id`.\n\nIf you specify multiple order IDs in the request, the response will return an array with each order object.\n",
        "operationId": "GetordersbyID",
        "deprecated": false,
        "parameters": [
          {
            "name": "order_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the order.",
            "example": "abcDEFgHiJklM1N-3OP9q",
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetOrdersById"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetOrdersByIdError"
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
      "GetOrdersById": {
        "type": "object",
        "properties": {
          "order": {
            "type": "string",
            "description": "Unique order ID.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "id": {
            "type": "string",
            "description": "Unique ID, same as order ID.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "reference": {
            "type": "string",
            "description": "External reference number.",
            "example": "ABC1234567-8910-11121D"
          },
          "buyerReference": {
            "type": "string",
            "nullable": true,
            "description": "Purchase order number used as a reference for the buyer.",
            "example": null
          },
          "ipAddress": {
            "type": "string",
            "format": "ipv4",
            "description": "IP address of the buyer.",
            "example": "00.000.00.000"
          },
          "completed": {
            "type": "boolean",
            "description": "Indicates if the order is completed.",
            "example": true
          },
          "changed": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp in milliseconds of last change.",
            "example": 1723838225894
          },
          "changedValue": {
            "type": "integer",
            "format": "int64",
            "description": "Unix timestamp in milliseconds for the changed value.",
            "example": 1723838225894
          },
          "changedInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Unix timestamp in seconds indicating the last change.",
            "example": 1723838225
          },
          "changedDisplay": {
            "type": "string",
            "description": "Human-readable date format.",
            "example": "8/16/24"
          },
          "changedDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "Date in ISO 8601 format.",
            "example": "2024-08-16"
          },
          "changedDisplayEmailEnhancements": {
            "type": "string",
            "description": "Date in a format commonly used in emails.",
            "example": "Aug 16, 2024"
          },
          "changedDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Date and time in a format commonly used in emails.",
            "example": "Aug 16, 2024 07:57:05 PM"
          },
          "language": {
            "type": "string",
            "description": "Two-letter language code (<a href=\"https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes\">ISO 639</a>) of the order language.",
            "example": "en"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates if the order is a live order or a test order; 'true' indicates a live order and 'false' indicates a test order.",
            "example": false
          },
          "currency": {
            "type": "string",
            "description": "Three-letter currency code (<a href=\"https://en.wikipedia.org/wiki/ISO_4217\">ISO 4217</a>) used in the order.",
            "example": "USD"
          },
          "payoutCurrency": {
            "type": "string",
            "description": "Currency used for payouts.",
            "example": "USD"
          },
          "quote": {
            "type": "string",
            "description": "Sales estimate or proposal associated with the order. If no quote is applicable, this property returns a 'null' value.",
            "example": null
          },
          "invoiceUrl": {
            "type": "string",
            "description": "URL of the associated invoice.",
            "example": "https://yourexamplestore.onfastspring.com/account/order/YES191015-1265-35113/invoice"
          },
          "siteId": {
            "type": "string",
            "description": "FastSpring-generated customer site or storefront ID.",
            "example": "LDN5SX4KBZCI2"
          },
          "account": {
            "type": "string",
            "description": "FastSpring-generated customer account ID.",
            "example": "abcDEFgHiJklM1N-3OP9q"
          },
          "total": {
            "type": "number",
            "format": "float",
            "description": "Total amount of the order.",
            "example": 10
          },
          "totalDisplay": {
            "type": "string",
            "description": "Display format of the total amount.",
            "example": "$10.00"
          },
          "totalInPayoutCurrency": {
            "type": "number",
            "format": "float",
            "description": "Total amount of the order in the payout currency.",
            "example": 10
          },
          "totalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Display format of the total amount in the payout currency.",
            "example": "$10.00"
          },
          "tax": {
            "type": "number",
            "format": "float",
            "description": "Tax amount.",
            "example": 0
          },
          "taxDisplay": {
            "type": "string",
            "description": "Display format of the tax amount.",
            "example": "$0.00"
          },
          "taxInPayoutCurrency": {
            "type": "number",
            "format": "float",
            "description": "Tax amount of the order in the payout currency.",
            "example": 0
          },
          "taxInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Display format of the tax amount in the payout currency.",
            "example": "$0.00"
          },
          "subtotal": {
            "type": "number",
            "format": "float",
            "description": "Subtotal amount of the order.",
            "example": 10
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Display format of the subtotal amount.",
            "example": "$10.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "format": "float",
            "description": "Subtotal amount of the order in the payout currency.",
            "example": 10
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Display format of the subtotal amount in the payout currency.",
            "example": "$10.00"
          },
          "discount": {
            "type": "number",
            "format": "float",
            "description": "Total amount of all discounts associated with the order. This displays in the currency associated with the subscription instance.",
            "example": 0
          },
          "discountDisplay": {
            "type": "string",
            "description": "Total amount of all discounts associated with the order, formatted for display in the associated currency.",
            "example": "$0.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "format": "float",
            "description": "Total amount of all discounts associated with the order, in your disbursement currency.",
            "example": 0
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Total amount of all discounts associated with the order, formatted for display in your FastSpring disbursements currency.",
            "example": "$0.00"
          },
          "discountWithTax": {
            "type": "number",
            "format": "float",
            "description": "Discount amount, including tax.",
            "example": 0
          },
          "discountWithTaxDisplay": {
            "type": "string",
            "description": "Discount amount including tax, formatted for display in the transaction's currency.",
            "example": "$0.00"
          },
          "discountWithTaxInPayoutCurrency": {
            "type": "number",
            "format": "float",
            "description": "Discount amount including tax, in the currency of your FastSpring disbursements.",
            "example": 0
          },
          "discountWithTaxInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Discount amount including tax, formatted for display in the currency of your FastSpring disbursements.",
            "example": "$0.00"
          },
          "billDescriptor": {
            "type": "string",
            "description": "The description information sent to the payment account for display on the customer's statement.",
            "example": "FS* fsprg.com"
          },
          "payment": {
            "type": "object",
            "description": "Details about the transaction's payment method.",
            "properties": {
              "type": {
                "type": "string",
                "description": "Payment method used for the order, such as \"paypal\", \"amazon\", \"applepay\", \"creditcard\", \"kakaopay\", \"test\", \"bank\", \"alipay\", \"purchase-order\", or \"free\".",
                "example": "test"
              },
              "creditcard": {
                "type": "string",
                "description": "Appears when payment.type = creditcard. Type of credit or debit card used for the order, such as \"visa\", \"mastercard\", \"amex\", \"discover\", \"jcb\", \"carteblanche\", \"dinersclub\", or \"unionpay\".",
                "example": "visa"
              },
              "cardEnding": {
                "type": "string",
                "description": "Appears when payment.type = bank. Type of bank transfer used to pay for the order, such as \"wire\", \"brazilwire\", \"ideal\", \"giropay\", \"sofort\", \"ecard\", \"sepa\", or \"alipay\".",
                "example": "wire"
              },
              "bank": {
                "type": "string",
                "description": "Appears when payment.type = creditcard. Last four digits of the card number used for the order.",
                "example": "4242"
              }
            }
          },
          "customer": {
            "type": "object",
            "properties": {
              "first": {
                "type": "string",
                "description": "Customer first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Customer last name.",
                "example": "Doe"
              },
              "email": {
                "type": "string",
                "format": "email",
                "description": "Customer email address.",
                "example": "first.last@domain.com"
              },
              "phone": {
                "type": "string",
                "description": "Customer phone number.",
                "example": "555-555-5555"
              },
              "subscribed": {
                "type": "boolean",
                "description": "Indicates if customer is subscribed to updates.",
                "example": true
              }
            }
          },
          "address": {
            "type": "object",
            "description": "Address information associated with the order.",
            "properties": {
              "city": {
                "type": "string",
                "description": "City, district, suburb, town, or village.",
                "example": "Santa Barbara"
              },
              "regionCode": {
                "type": "string",
                "description": "Two-letter ISO code of the US state.",
                "example": "CA"
              },
              "regionDisplay": {
                "type": "string",
                "description": "State or region, formatted for display.",
                "example": "California"
              },
              "region": {
                "type": "string",
                "description": "State or region.",
                "example": "California"
              },
              "postalCode": {
                "type": "string",
                "description": "ZIP or postal code.",
                "example": 93101
              },
              "country": {
                "type": "string",
                "description": "Two-letter country code.",
                "example": "US"
              },
              "display": {
                "type": "string",
                "description": "String of address information formatted for display.",
                "example": "Santa Barbara, California, 93101, US"
              }
            }
          },
          "recipients": {
            "type": "array",
            "description": "Customer and gift recipient information. This differs from customer and address information on gift purchases.",
            "items": {
              "type": "object",
              "properties": {
                "recipient": {
                  "type": "object",
                  "properties": {
                    "first": {
                      "type": "string",
                      "description": "Recipient first name.",
                      "example": "First"
                    },
                    "last": {
                      "type": "string",
                      "description": "Recipient last name.",
                      "example": "Last"
                    },
                    "email": {
                      "type": "string",
                      "description": "Recipient email address.",
                      "example": "first.last@domain.com"
                    },
                    "company": {
                      "type": "string",
                      "description": "Recipient associated company.",
                      "example": "Company Name"
                    },
                    "phone": {
                      "type": "string",
                      "description": "Recipient phone number.",
                      "example": "555-555-5555"
                    },
                    "subscribed": {
                      "type": "boolean",
                      "description": "Indicates if customer is subscribed to updates.",
                      "example": true
                    },
                    "account": {
                      "type": "string",
                      "description": "FastSpring-generated customer account ID.",
                      "example": "abcDEFgHiJklM1N-3OP9q"
                    },
                    "address": {
                      "type": "object",
                      "properties": {
                        "city": {
                          "type": "string",
                          "description": "City, district, suburb, town, or village.",
                          "example": "Santa Barbara"
                        },
                        "regionCode": {
                          "type": "string",
                          "description": "Two-letter ISO code of the US state.",
                          "example": "CA"
                        },
                        "regionDisplay": {
                          "type": "string",
                          "description": "State or region, formatted for display.",
                          "example": "California"
                        },
                        "region": {
                          "type": "string",
                          "description": "State or region.",
                          "example": "California"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "ZIP or postal code.",
                          "example": 93101
                        },
                        "country": {
                          "type": "string",
                          "description": "Two-letter country code.",
                          "example": "US"
                        },
                        "display": {
                          "type": "string",
                          "description": "String of address information formatted for display.",
                          "example": "Santa Barbara, California, 93101, US"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "notes": {
            "type": "array",
            "description": "Internal order notes. You can add notes within the app.",
            "example": "This is my note."
          },
          "items": {
            "type": "array",
            "description": "List of items in the order.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Unique identifier for the product.",
                  "example": "furious-falcon"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product ordered.",
                  "example": 1
                },
                "display": {
                  "type": "string",
                  "description": "Display name of the product.",
                  "example": "Furious Falcon"
                },
                "sku": {
                  "type": "string",
                  "description": "Stock keeping unit (SKU) identifier if available.",
                  "example": null
                },
                "imageUrl": {
                  "type": "string",
                  "description": "URL link to the product image.",
                  "example": "https://image.com/furious-falcon-logo.png"
                },
                "shortDisplay": {
                  "type": "string",
                  "description": "Abbreviated or short display name for the product.",
                  "example": "Furious Falcon"
                },
                "subtotal": {
                  "type": "number",
                  "description": "Subtotal amount for this product in the base currency.",
                  "example": 10
                },
                "subtotalDisplay": {
                  "type": "string",
                  "description": "Formatted display for subtotal.",
                  "example": "$10.00"
                },
                "subtotalInPayoutCurrency": {
                  "type": "number",
                  "description": "Subtotal amount in payout currency.",
                  "example": 10
                },
                "subtotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for subtotal in payout currency.",
                  "example": "$10.00"
                },
                "discount": {
                  "type": "number",
                  "description": "Discount amount for the product in base currency.",
                  "example": 0
                },
                "discountDisplay": {
                  "type": "string",
                  "description": "Formatted display for the discount amount.",
                  "example": "$0.00"
                },
                "discountInPayoutCurrency": {
                  "type": "number",
                  "description": "Discount amount in payout currency.",
                  "example": 0
                },
                "discountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for the discount amount in payout currency.",
                  "example": "$0.00"
                },
                "isAddon": {
                  "type": "boolean",
                  "description": "Indicates if the item is an add-on product.",
                  "example": true
                },
                "fulfillments": {
                  "type": "object",
                  "description": "Details about the fulfillment of the item.",
                  "example": {}
                },
                "driver": {
                  "type": "object",
                  "description": "Driver information for cross-sell or upsell features.",
                  "properties": {
                    "type": {
                      "type": "string",
                      "description": "Type of driver, such as cross-sell or upsell.",
                      "example": "cross-sell"
                    },
                    "path": {
                      "type": "string",
                      "description": "Path or route identifier for the driver.",
                      "example": "example-monthly-subscription"
                    }
                  }
                },
                "withholdings": {
                  "type": "object",
                  "description": "Details on withholdings applicable to the item.",
                  "properties": {
                    "taxWithholdings": {
                      "type": "boolean",
                      "description": "Indicates if tax withholdings apply.",
                      "example": false
                    }
                  }
                },
                "proratedItemChangeAmount": {
                  "type": "number",
                  "description": "Prorated change amount for the item in base currency.",
                  "example": 0
                },
                "proratedItemChangeAmountDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated change amount.",
                  "example": "$0.00"
                },
                "proratedItemChangeAmountInPayoutCurrency": {
                  "type": "number",
                  "description": "Prorated change amount in payout currency.",
                  "example": 0
                },
                "proratedItemChangeAmountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated change amount in payout currency.",
                  "example": "$0.00"
                },
                "proratedItemProratedCharge": {
                  "type": "number",
                  "description": "Prorated charge amount in base currency.",
                  "example": 0
                },
                "proratedItemProratedChargeDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated charge amount.",
                  "example": "$0.00"
                },
                "proratedItemProratedChargeInPayoutCurrency": {
                  "type": "number",
                  "description": "Prorated charge amount in payout currency.",
                  "example": 0
                },
                "proratedItemProratedChargeInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated charge amount in payout currency.",
                  "example": "$0.00"
                },
                "proratedItemCreditAmount": {
                  "type": "number",
                  "description": "Prorated credit amount in base currency.",
                  "example": 0
                },
                "proratedItemCreditAmountDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated credit amount.",
                  "example": "$0.00"
                },
                "proratedItemCreditAmountInPayoutCurrency": {
                  "type": "number",
                  "description": "Prorated credit amount in payout currency.",
                  "example": 0
                },
                "proratedItemCreditAmountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated credit amount in payout currency.",
                  "example": "$0.00"
                },
                "proratedItemTaxAmount": {
                  "type": "number",
                  "description": "Prorated tax amount in base currency.",
                  "example": 0
                },
                "proratedItemTaxAmountDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated tax amount.",
                  "example": "$0.00"
                },
                "proratedItemTaxAmountInPayoutCurrency": {
                  "type": "number",
                  "description": "Prorated tax amount in payout currency.",
                  "example": 0
                },
                "proratedItemTaxAmountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated tax amount in payout currency.",
                  "example": "$0.00"
                },
                "proratedItemTotal": {
                  "type": "number",
                  "description": "Total prorated amount in base currency.",
                  "example": 0
                },
                "proratedItemTotalDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated total amount.",
                  "example": "$0.00"
                },
                "proratedItemTotalInPayoutCurrency": {
                  "type": "number",
                  "description": "Total prorated amount in payout currency.",
                  "example": 0
                },
                "proratedItemTotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display for prorated total amount in payout currency.",
                  "example": "$0.00"
                }
              }
            }
          },
          "action": {
            "type": "string",
            "description": "The action performed by the API.",
            "example": "order.get"
          },
          "result": {
            "type": "string",
            "description": "The result of the action (e.g., success or error).",
            "example": "success"
          }
        }
      },
      "GetOrdersByIdError": {
        "type": "object",
        "properties": {
          "orders": {
            "type": "array",
            "description": "List of order responses from the API.",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed by the API on the order.",
                  "example": "order.get"
                },
                "order": {
                  "type": "string",
                  "description": "Unique identifier for the order.",
                  "example": "abcDEFgHiJklM1N-3OP9qp3R"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "description": "Details of any errors encountered during the API call.",
                  "properties": {
                    "order": {
                      "type": "string",
                      "description": "Error message indicating the specific issue with the order.",
                      "example": "Not found"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
```
Update order tags and attributes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update order tags and attributes

Updates order tags and attributes.

Active subscriptions with these attributes will automatically update to reflect the new tags and attributes.      


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Orders",
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
      "name": "Orders",
      "description": "List, retrieve, and update orders.     \n"
    }
  ],
  "paths": {
    "/orders": {
      "post": {
        "summary": "Update order tags and attributes",
        "tags": [
          "Orders"
        ],
        "description": "Updates order tags and attributes.\n\nActive subscriptions with these attributes will automatically update to reflect the new tags and attributes.      \n",
        "operationId": "updateordertagsandattributes",
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateOrderTagsRequest"
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
                  "$ref": "#/components/schemas/UpdateOrderTagsResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "oneOf": [
                    {
                      "$ref": "#/components/schemas/UpdateOrderTagsError400NotValid"
                    },
                    {
                      "$ref": "#/components/schemas/UpdateOrderTagsError400NotFound"
                    }
                  ]
                },
                "examples": {
                  "NotFoundExample": {
                    "summary": "Order Not Found",
                    "value": {
                      "orders": [
                        {
                          "action": "order.update",
                          "order": "[0]",
                          "result": "error",
                          "error": {
                            "order": "Not found"
                          }
                        }
                      ]
                    }
                  },
                  "NotValidExample": {
                    "summary": "Invalid Product in Order",
                    "value": {
                      "orders": [
                        {
                          "action": "order.update",
                          "order": "abcDEFgHiJklM1N-3OP9q",
                          "result": "error",
                          "error": {
                            "items.product-path": "Product is not specified as a valid item in the order"
                          }
                        }
                      ]
                    }
                  }
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
      "UpdateOrderTagsRequest": {
        "type": "object",
        "required": [
          "orders"
        ],
        "properties": {
          "orders": {
            "type": "array",
            "items": {
              "type": "object",
              "required": [
                "order"
              ],
              "properties": {
                "order": {
                  "type": "string",
                  "description": "The order ID to be updated.",
                  "example": "abcDEFgHiJklM1N-3OP9q"
                },
                "tags": {
                  "type": "object",
                  "properties": {
                    "TagKey1": {
                      "type": "string",
                      "description": "The order tag to be added or updated.",
                      "example": "TagValue1"
                    },
                    "TagKey2": {
                      "type": "string",
                      "description": "The order tag to be added or updated.",
                      "example": "TagValue2"
                    }
                  }
                },
                "items": {
                  "type": "array",
                  "items": {
                    "type": "object",
                    "properties": {
                      "product": {
                        "type": "string",
                        "description": "The product path of the product whose attributes will be added or updated.",
                        "example": "furious-falcon"
                      },
                      "attributes": {
                        "type": "object",
                        "properties": {
                          "AttributeKey1": {
                            "type": "string",
                            "description": "The attribute to be added or updated.",
                            "example": "AttributeValue1"
                          },
                          "AttributeKey2": {
                            "type": "string",
                            "description": "The attribute to be added or updated.",
                            "example": "AttributeValue2"
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      },
      "UpdateOrderTagsResponse": {
        "type": "object",
        "properties": {
          "orders": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed by the API call.",
                  "example": "order.update"
                },
                "result": {
                  "type": "string",
                  "description": "Indicates that the request has succeeded or failed.",
                  "example": "success"
                },
                "order": {
                  "type": "string",
                  "description": "The unique order identifier.",
                  "example": "abcDEFgHiJklM1N-3OP9q"
                }
              }
            }
          }
        }
      },
      "UpdateOrderTagsError400NotFound": {
        "title": "Order Not Found",
        "type": "object",
        "properties": {
          "orders": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed by the API call.",
                  "example": "order.update"
                },
                "order": {
                  "type": "string",
                  "description": "The unique order identifier.",
                  "example": "[0]"
                },
                "result": {
                  "type": "string",
                  "description": "The outcome of the API request, indicating an error.",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "properties": {
                    "order": {
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
      },
      "UpdateOrderTagsError400NotValid": {
        "title": "Order Not Valid",
        "type": "object",
        "properties": {
          "orders": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed by the API call.",
                  "example": "order.update"
                },
                "order": {
                  "type": "string",
                  "description": "The unique order identifier.",
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
                    "items.product-path": {
                      "type": "string",
                      "description": "A message describing the error.",
                      "example": "Product is not specified as a valid item in the order"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
```