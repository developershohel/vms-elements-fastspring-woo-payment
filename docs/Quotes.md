Quotes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Quotes

Use the /quotes API endpoint to efficiently process and manage your B2B orders and billing capabilities, including quote creation, digital invoicing, and order management. With this endpoint, you can:

* [Create a quote](https://developer.fastspring.com/reference/create-a-quote)
* [List all quotes](https://developer.fastspring.com/reference/list-all-quotes)
* [Retrieve a quote](https://developer.fastspring.com/reference/retrieve-a-quote)
* [Update a quote](https://developer.fastspring.com/reference/update-a-quote)
* [Cancel a quote](https://developer.fastspring.com/reference/cancel-a-quote)

Unlike other FastSpring APIs, this API utilizes a POST request for creating resources, and a PUT request for updating them.

# DefaultValue Attribute

Most attributes in the /quotes API have a default value assigned from the preexisting settings, products, and coupons in your FastSpring Store.

The DefaultValue is used in POST and PUT request bodies. Attributes with a store-defined default value will be marked in the **Note** section for the request attributes.

All attributes that have a DefaultValue associated with them are optional request values; these override default value settings. If you wish to use default values, do not include optional request values in the body.

## Missing "Notes" Attribute

```text
{
   ...
   "name": "A descriptive name.",
   "recipient": {...},
   ...
}
```

## Set "Notes" Attribute to Null

```text
{
   ...
   "name": "A descriptive name.",
   "notes": null, 
   "recipient": {...},
   ...
}
```

## Override the Default Value

The example below demonstrates overriding the DefaultValue configured in your store for the notes attribute in a CreateQuote request.

```text
{
   "name": "The Quote name",
   "notes": "This value will override the default notes store-level value.",
   "recipient": {...},
   ...
}
```

## Override the Default Value to an Empty String

The example below demonstrates overriding your Store’s DefaultValue for the notes attribute to an empty string.

```text
{
   "name": "The Quote name",
   "notes": "",
   "recipient": {...},
   ...
}
```

Create a quote

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create a quote

Creates a new quote with an open status.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Quotes",
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
      "name": "Quotes",
      "description": "Create, list, retrieve, update, and cancel quotes.\n"
    }
  ],
  "paths": {
    "/quotes": {
      "post": {
        "summary": "Create a quote",
        "tags": [
          "Quotes"
        ],
        "operationId": "createQuote",
        "description": "Creates a new quote with an open status.",
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateQuoteRequest"
              }
            }
          },
          "required": true
        },
        "responses": {
          "201": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/QuoteResponse"
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
      "CreateQuoteRequest": {
        "title": "Create a quote",
        "type": "object",
        "required": [
          "items",
          "name",
          "recipient",
          "recipientAddress"
        ],
        "properties": {
          "items": {
            "type": "array",
            "minItems": 1,
            "maxItems": 2147483647,
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "description": "Item definition for the quote.",
              "required": [
                "product"
              ],
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "quantity": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in base currency.",
                  "example": 10
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "The name or label associated with the quote.",
            "example": "Name of the quote"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient (contact) information for the quote.",
            "required": [
              "email",
              "first",
              "last"
            ],
            "properties": {
              "company": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "nullable": true,
                "description": "Phone number of the contact, if any.",
                "example": "5555551234"
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "required": [
              "country",
              "postalCode"
            ],
            "properties": {
              "addressLine1": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "nullable": true,
                "description": "Second line of the address, if any.",
                "example": "Suite 101"
              },
              "city": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "City or municipality.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "minLength": 2,
                "maxLength": 2,
                "description": "2-letter country code.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Postal or ZIP code.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "State, province, or region.",
                "example": "Example Region"
              }
            }
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "minLength": 0,
            "maxLength": 255,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "currency": {
            "type": "string",
            "minLength": 3,
            "maxLength": 3,
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "expirationDateDays": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "maximum": 90,
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ],
            "example": "ON_PAYMENT"
          },
          "notes": {
            "type": "string",
            "minLength": 0,
            "maxLength": 5000,
            "description": "Additional notes for the quote.",
            "example": "This is a Note"
          },
          "netTermsDays": {
            "type": "integer",
            "format": "int32",
            "description": "Number of days for net payment terms."
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects attached to the quote.",
            "items": {
              "type": "object",
              "description": "Key-value pair for tags.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "taxId": {
            "type": "string",
            "minLength": 0,
            "maxLength": 255,
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "minLength": 0,
            "maxLength": 255,
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "minLength": 0,
            "maxLength": 25,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          }
        }
      },
      "QuoteResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "buyerGenerated": {
            "type": "boolean",
            "description": "Indicates if the quote was created by the buyer.",
            "example": false
          },
          "copyNotesToInvoice": {
            "type": "boolean",
            "description": "Determines whether notes should be copied to the invoice.",
            "example": false
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was created.",
            "example": "2024-09-09T14:51:34.814+00:00"
          },
          "createdBy": {
            "type": "object",
            "description": "Information about the user who created this quote.",
            "properties": {
              "company": {
                "type": "string",
                "description": "Company name of the creator.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Email address of the creator.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "First name of the creator.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Last name of the creator.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "nullable": true,
                "description": "Phone number of the creator.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID associated with the creator, if available.",
                "example": "abCDEFGHijkLmNOpQr_S"
              }
            }
          },
          "currency": {
            "type": "string",
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount in base currency.",
            "example": 40
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in base currency.",
            "example": "$40.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "description": "Discount amount in the payout currency.",
            "example": 40
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in the payout currency.",
            "example": "$40.00"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote expires.",
            "example": "2024-10-09T14:51:34.814+00:00"
          },
          "expirationDateDays": {
            "type": "integer",
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "example": "ON_PAYMENT",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ]
          },
          "items": {
            "type": "array",
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "customPrice": {
                  "type": "boolean",
                  "description": "Indicates if the price is custom.",
                  "example": false
                },
                "display": {
                  "type": "string",
                  "description": "Display name for the product.",
                  "example": "Add-on Subscription"
                },
                "image": {
                  "type": "string",
                  "nullable": true,
                  "description": "Image filename or URL, if applicable.",
                  "example": null
                },
                "intervalCount": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of intervals (e.g., for a subscription).",
                  "example": null
                },
                "period": {
                  "type": "string",
                  "nullable": true,
                  "description": "Billing period for the product (e.g., Monthly).",
                  "example": "Monthly"
                },
                "periodDays": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of days in the billing period.",
                  "example": null
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "renewIntoProducts": {
                  "type": "array",
                  "uniqueItems": true,
                  "description": "Array of product(s) into which this item can renew.",
                  "items": {
                    "type": "object",
                    "description": "Details of the product that this item renews into.",
                    "properties": {
                      "renewIntoProductId": {
                        "type": "string",
                        "description": "Product ID into which the item renews.",
                        "example": "DG4UH337PVYDC33XEP3ZH3JHD"
                      },
                      "renewIntoPath": {
                        "type": "string",
                        "description": "Path or identifier for the renewing product.",
                        "example": "video-subscription"
                      },
                      "renewIntoPrice": {
                        "type": "number",
                        "format": "float",
                        "description": "Price for the renewing product.",
                        "example": 9.99
                      },
                      "renewIntoPeriod": {
                        "type": "string",
                        "description": "Period for the renewing product (e.g., Weekly, Monthly).",
                        "example": "Weekly"
                      },
                      "renewIntoLevel": {
                        "type": "integer",
                        "format": "int32",
                        "description": "Level or tier for the renewal.",
                        "example": 1
                      },
                      "renewIntoIntervalCount": {
                        "type": "string",
                        "description": "Interval count for the renewal (e.g., \"2\" months).",
                        "example": "2"
                      },
                      "renewIntoUpcomingProduct": {
                        "type": "string",
                        "description": "Upcoming product name or handle after renewal.",
                        "example": "audio-subscription"
                      }
                    }
                  }
                },
                "taxes": {
                  "type": "array",
                  "description": "Tax details for this item.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "taxValue": {
                        "type": "number",
                        "description": "Tax amount for this item.",
                        "example": 0
                      },
                      "totalTaxable": {
                        "type": "number",
                        "description": "Total taxable amount for this item.",
                        "example": 360
                      }
                    }
                  }
                },
                "trialDays": {
                  "type": "integer",
                  "nullable": true,
                  "description": "Number of trial days, if applicable.",
                  "example": null
                },
                "subscription": {
                  "type": "boolean",
                  "description": "Indicates if the item is a subscription.",
                  "example": true
                },
                "unitTrialPrice": {
                  "type": "number",
                  "description": "Trial price per unit, if applicable.",
                  "example": 0
                },
                "trialPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Trial price without tax, if any.",
                  "example": null
                },
                "trialExtendedPrice": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended price for the trial, if any.",
                  "example": null
                },
                "trialExtendedPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial price without tax.",
                  "example": null
                },
                "trialUnitTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Tax per unit during trial.",
                  "example": null
                },
                "trialExtendedTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial tax, if any.",
                  "example": null
                },
                "driver": {
                  "type": "string",
                  "nullable": true,
                  "description": "Driver or source reference, if any.",
                  "example": null
                },
                "type": {
                  "type": "string",
                  "nullable": true,
                  "description": "Additional type info.",
                  "example": null
                },
                "unitCouponDiscount": {
                  "type": "number",
                  "description": "Discount per unit from a coupon.",
                  "example": 0
                },
                "unitCouponDiscountDisplay": {
                  "type": "string",
                  "description": "Display format of coupon discount per unit.",
                  "example": "$0.00"
                },
                "unitCouponDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Coupon discount in the payout currency.",
                  "example": 0
                },
                "unitCouponDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the coupon discount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "description": "Amount of discount per unit in base currency.",
                  "example": 20
                },
                "unitDiscountWithoutTax": {
                  "type": "number",
                  "description": "Per-unit discount before tax.",
                  "example": 20
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$20.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit discount in payout currency.",
                  "example": 20
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the discount in payout currency.",
                  "example": "$20.00"
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in the base currency.",
                  "example": 200
                },
                "unitListPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price per unit in base currency.",
                  "example": "$200.00"
                },
                "unitListPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit list price in payout currency.",
                  "example": 200
                },
                "unitListPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price in payout currency.",
                  "example": "$200.00"
                },
                "unitPrice": {
                  "type": "number",
                  "description": "Final price per unit in base currency.",
                  "example": 180
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price.",
                  "example": "$180.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Final price per unit in the payout currency.",
                  "example": 180
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price in payout currency.",
                  "example": "$180.00"
                },
                "unitPriceWithoutTax": {
                  "type": "number",
                  "description": "Final unit price without tax.",
                  "example": 180
                },
                "unitPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the unit price without tax.",
                  "example": "$180.00"
                },
                "unitListPriceWithoutTax": {
                  "type": "number",
                  "description": "Original list price per unit before tax.",
                  "example": 200
                },
                "unitListPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price before tax.",
                  "example": "$200.00"
                },
                "grossModeOption": {
                  "type": "string",
                  "nullable": true,
                  "description": "Gross mode option, if applicable.",
                  "example": null
                },
                "taxMode": {
                  "type": "string",
                  "description": "Whether tax is NET or GROSS for this item.",
                  "example": "NET"
                },
                "priceDetail": {
                  "type": "object",
                  "description": "Detailed breakdown of the item’s pricing.",
                  "properties": {
                    "path": {
                      "type": "string",
                      "description": "Identifier or path for this item’s pricing context.",
                      "example": "add-on-subscription"
                    },
                    "quantity": {
                      "type": "integer",
                      "description": "Quantity used for this price detail.",
                      "example": 2
                    },
                    "plans": {
                      "type": "array",
                      "description": "Array of plan objects associated with pricing.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "path": {
                            "type": "string",
                            "description": "Plan’s product path.",
                            "example": "add-on-subscription"
                          },
                          "interval": {
                            "type": "string",
                            "description": "Interval name (e.g., month, year).",
                            "example": "month"
                          },
                          "intervalLength": {
                            "type": "integer",
                            "description": "Number of intervals per billing cycle.",
                            "example": 1
                          },
                          "prices": {
                            "type": "array",
                            "description": "Detailed price lines for each plan.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "path": {
                                  "type": "string",
                                  "description": "Product path for pricing context.",
                                  "example": "add-on-subscription"
                                },
                                "showUnitListPrice": {
                                  "type": "number",
                                  "description": "Displayed list price per unit.",
                                  "example": 200
                                },
                                "showUnitNetPrice": {
                                  "type": "number",
                                  "description": "Displayed net price per unit.",
                                  "example": 180
                                },
                                "showExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Displayed extended net price for all units.",
                                  "example": 360
                                },
                                "showExtendedListPrice": {
                                  "type": "number",
                                  "description": "Displayed extended list price.",
                                  "example": 400
                                },
                                "showUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed discount amount per unit.",
                                  "example": 20
                                },
                                "showExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed total discount across all units.",
                                  "example": 40
                                },
                                "showExtendedTaxes": {
                                  "type": "number",
                                  "description": "Displayed total extended taxes.",
                                  "example": 0
                                },
                                "showTaxPercent": {
                                  "type": "number",
                                  "description": "Displayed tax percentage.",
                                  "example": 0
                                },
                                "subscriptionUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price if this item is a subscription.",
                                  "example": 200
                                },
                                "subscriptionExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for the subscription.",
                                  "example": 360
                                },
                                "subscriptionUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per subscription unit.",
                                  "example": 20
                                },
                                "subscriptionTaxMode": {
                                  "type": "string",
                                  "description": "Tax mode for the subscription (NET or GROSS).",
                                  "example": "NET"
                                },
                                "unitListPrice": {
                                  "type": "number",
                                  "description": "Raw list price per unit in base currency.",
                                  "example": 200
                                },
                                "unitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit in base currency.",
                                  "example": 180
                                },
                                "unitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per unit in base currency.",
                                  "example": 20
                                },
                                "extendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price for all units.",
                                  "example": 400
                                },
                                "extendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for all units.",
                                  "example": 360
                                },
                                "extendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended total discount across all units.",
                                  "example": 40
                                },
                                "withTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price with tax included.",
                                  "example": 200
                                },
                                "withTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Per-unit net price with tax included.",
                                  "example": 180
                                },
                                "withTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price with tax included.",
                                  "example": 400
                                },
                                "withTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price with tax included.",
                                  "example": 360
                                },
                                "withTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount with tax included.",
                                  "example": 40
                                },
                                "withoutTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit without tax.",
                                  "example": 200
                                },
                                "withoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit without tax.",
                                  "example": 180
                                },
                                "withoutTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price without tax.",
                                  "example": 400
                                },
                                "withoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price without tax.",
                                  "example": 360
                                },
                                "withoutTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount without tax.",
                                  "example": 40
                                },
                                "discountPercent": {
                                  "type": "number",
                                  "description": "Percentage discount applied to this item.",
                                  "example": 10
                                },
                                "withTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount with tax.",
                                  "example": 40
                                },
                                "withoutTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount without tax.",
                                  "example": 40
                                },
                                "productDiscountPercent": {
                                  "type": "number",
                                  "description": "Discount percent specific to the product.",
                                  "example": 0
                                },
                                "roundedWithoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net unit price without tax.",
                                  "example": 180
                                },
                                "roundedWithoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded extended net price without tax.",
                                  "example": 360
                                },
                                "roundedExtendedTaxes": {
                                  "type": "number",
                                  "description": "Rounded extended taxes.",
                                  "example": 0
                                },
                                "roundedWithTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net price with tax included.",
                                  "example": 360
                                },
                                "unitTaxes": {
                                  "type": "number",
                                  "description": "Tax per unit.",
                                  "example": 0
                                },
                                "extendedTaxes": {
                                  "type": "number",
                                  "description": "Extended total taxes.",
                                  "example": 0
                                },
                                "taxPercent": {
                                  "type": "number",
                                  "description": "Tax percentage.",
                                  "example": 0
                                },
                                "sourceListPrice": {
                                  "type": "number",
                                  "description": "Source or original list price.",
                                  "example": 200
                                },
                                "sourceCurrency": {
                                  "type": "string",
                                  "description": "Currency code used by the source price.",
                                  "example": "USD"
                                },
                                "lineNumber": {
                                  "type": "string",
                                  "description": "Unique identifier for this price line.",
                                  "example": "LNNS3BGRMB65JEK1FF3N9W9FEEKC"
                                },
                                "withholdingAmount": {
                                  "type": "number",
                                  "description": "Withholding amount in base currency.",
                                  "example": 0
                                },
                                "withholdingAmountUSD": {
                                  "type": "number",
                                  "description": "Withholding amount in USD, if different.",
                                  "example": 0
                                },
                                "rules": {
                                  "type": "array",
                                  "description": "Array of pricing rules applied.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "name": {
                                        "type": "string",
                                        "description": "Name of the rule.",
                                        "example": "DetermineCurrencyCode"
                                      },
                                      "reason": {
                                        "type": "string",
                                        "description": "Reason for applying this rule.",
                                        "example": "Input currency"
                                      },
                                      "outputCurrency": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Currency determined by the rule.",
                                        "example": "USD"
                                      },
                                      "trial": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Indicates if a trial was factored.",
                                        "example": "false"
                                      },
                                      "endPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the final price after the rule.",
                                        "example": "$200.00 USD"
                                      },
                                      "priceModel": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Pricing model (e.g., \"PER_UNIT\").",
                                        "example": "PER_UNIT"
                                      },
                                      "startPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the price before the rule.",
                                        "example": null
                                      },
                                      "description": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Brief explanation of the rule.",
                                        "example": "Load price"
                                      },
                                      "operation": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Operation performed by the rule.",
                                        "example": "+ 0.0000"
                                      },
                                      "operation2": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Secondary operation detail."
                                      },
                                      "taxMode": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "NET or GROSS tax mode indicated by the rule.",
                                        "example": "NET"
                                      },
                                      "taxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Tax rate used by the rule.",
                                        "example": "0.0000"
                                      },
                                      "taxExempt": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Whether the item is tax exempt.",
                                        "example": "false"
                                      },
                                      "unitPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit price in the context of this rule.",
                                        "example": "180.0000"
                                      },
                                      "unitTotal": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit total used by the rule."
                                      },
                                      "effectiveTaxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Effective tax rate after the rule."
                                      },
                                      "quantity": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Quantity used by the rule.",
                                        "example": "2"
                                      },
                                      "withTaxNetUnitRoundAmount": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Rounding amount for net unit price with tax."
                                      }
                                    }
                                  }
                                },
                                "effectiveTaxMode": {
                                  "type": "string",
                                  "description": "Final tax mode determined by the plan.",
                                  "example": "NET"
                                },
                                "dateLimitsEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if date limits were enforced.",
                                  "example": false
                                },
                                "discountTiers": {
                                  "type": "array",
                                  "description": "Array of discount tier objects for this plan.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "min": {
                                        "type": "integer",
                                        "description": "Minimum quantity for this tier.",
                                        "example": 1
                                      },
                                      "withTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price with tax in this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price without tax for this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount without tax.",
                                        "example": 20
                                      },
                                      "withTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount with tax included.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage for this tier.",
                                        "example": 10
                                      },
                                      "type": {
                                        "type": "string",
                                        "description": "Discount type (e.g., VOLUME_PERCENT_OFF).",
                                        "example": "VOLUME_PERCENT_OFF"
                                      }
                                    }
                                  }
                                },
                                "discounts": {
                                  "type": "array",
                                  "description": "Discounts applied at the plan level.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "discountType": {
                                        "type": "string",
                                        "description": "Type of discount (e.g., \"VOLUME_PERCENT_OFF\").",
                                        "example": "VOLUME_PERCENT_OFF"
                                      },
                                      "discountDuration": {
                                        "type": "integer",
                                        "description": "How long (in intervals) this discount is valid.",
                                        "example": 5
                                      },
                                      "discountPath": {
                                        "type": "string",
                                        "description": "Path or identifier for this discount.",
                                        "example": "add-on-subscription"
                                      },
                                      "discountUnitAmount": {
                                        "type": "number",
                                        "description": "Discount amount per unit in base currency.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage applied.",
                                        "example": 10
                                      }
                                    }
                                  }
                                },
                                "withTaxStoreCurrencyExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price (store currency) with tax.",
                                  "example": 360
                                },
                                "withTaxUSDExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price in USD with tax.",
                                  "example": 360
                                },
                                "taxCode": {
                                  "type": "string",
                                  "description": "Code representing the tax classification.",
                                  "example": "DV021010"
                                },
                                "taxFormat": {
                                  "type": "string",
                                  "description": "Format or category of tax (e.g., \"DIGITAL_ONLY\").",
                                  "example": "DIGITAL_ONLY"
                                },
                                "pricingPlanRenew": {
                                  "type": "string",
                                  "description": "Renewal policy for the pricing plan (e.g., \"auto\").",
                                  "example": "auto"
                                },
                                "customPrice": {
                                  "type": "boolean",
                                  "description": "Indicates if a custom price was used.",
                                  "example": false
                                },
                                "paidTrial": {
                                  "type": "boolean",
                                  "description": "Indicates if the trial period is paid.",
                                  "example": false
                                },
                                "paymentRequired": {
                                  "type": "boolean",
                                  "description": "Whether payment is required for the plan.",
                                  "example": true
                                },
                                "reactivationEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if reactivation is allowed.",
                                  "example": false
                                },
                                "reactivationExpirationDays": {
                                  "type": "integer",
                                  "description": "Days before a reactivation link expires.",
                                  "example": 0
                                },
                                "taxExemptedUnitAmount": {
                                  "type": "number",
                                  "description": "Per-unit amount exempted from tax.",
                                  "example": 0
                                },
                                "taxExemptedExtendedAmount": {
                                  "type": "number",
                                  "description": "Extended total amount exempted from tax.",
                                  "example": 0
                                },
                                "withoutTaxExemptionUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit when not tax-exempt.",
                                  "example": 180
                                },
                                "withoutTaxExemptionUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit when not tax-exempt.",
                                  "example": 200
                                },
                                "quantity": {
                                  "type": "integer",
                                  "description": "Quantity used in pricing calculations.",
                                  "example": 2
                                },
                                "discountPath": {
                                  "type": "string",
                                  "description": "Path for applying the discount, if any.",
                                  "example": "add-on-subscription"
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                },
                "quantityBehavior": {
                  "type": "string",
                  "description": "Determines how quantity changes are handled.",
                  "example": "allow"
                },
                "quantityDefault": {
                  "type": "integer",
                  "description": "Default quantity if none is specified.",
                  "example": 1
                },
                "discountDuration": {
                  "type": "integer",
                  "description": "Number of intervals the discount remains valid.",
                  "example": 5
                },
                "cancellationChoice": {
                  "type": "string",
                  "description": "Cancellation policy (e.g., AFTER_LAST_NOTIFICATION).",
                  "example": "AFTER_LAST_NOTIFICATION"
                },
                "startNewTerm": {
                  "type": "boolean",
                  "description": "Whether a new subscription term should begin.",
                  "example": false
                },
                "subscriptionCancelDisabled": {
                  "type": "boolean",
                  "description": "Indicates if subscription cancellation is disabled.",
                  "example": false
                },
                "notifications": {
                  "type": "array",
                  "description": "Notification rules for different events.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "notificationType": {
                        "type": "string",
                        "description": "Type of notification (e.g., PAYMENT_REMINDER).",
                        "example": "TRIAL_CONVERSION_REMINDER"
                      },
                      "enabled": {
                        "type": "boolean",
                        "description": "Indicates if the notification is enabled.",
                        "example": false
                      },
                      "intervals": {
                        "type": "array",
                        "description": "Interval definitions for sending notifications.",
                        "items": {
                          "type": "object",
                          "properties": {
                            "intervalUnit": {
                              "type": "string",
                              "description": "Unit of interval (day, week, etc.).",
                              "example": "day"
                            },
                            "intervalLength": {
                              "type": "integer",
                              "description": "Length of each interval.",
                              "example": 3
                            },
                            "intervalCount": {
                              "type": "integer",
                              "nullable": true,
                              "description": "Number of times to repeat, if applicable."
                            }
                          }
                        }
                      },
                      "firstIntervalUnit": {
                        "type": "string",
                        "description": "Unit for the first notification interval.",
                        "example": "day"
                      },
                      "firstIntervalLength": {
                        "type": "integer",
                        "description": "Length of the first interval.",
                        "example": 3
                      },
                      "firstIntervalCount": {
                        "type": "integer",
                        "nullable": true,
                        "description": "How many times the first interval repeats."
                      }
                    }
                  }
                },
                "productDescriptions": {
                  "type": "object",
                  "description": "Localized display and summary of the product.",
                  "properties": {
                    "display": {
                      "type": "object",
                      "description": "Localized display text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "Add-on Subscription"
                        }
                      }
                    },
                    "summary": {
                      "type": "object",
                      "description": "Localized summary text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "This is my product summary"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "Name or label for the quote.",
            "example": "Name of the quote"
          },
          "notes": {
            "type": "string",
            "nullable": true,
            "description": "Additional notes for the quote.",
            "example": null
          },
          "netTermsDays": {
            "type": "integer",
            "description": "Number of days for net payment terms.",
            "example": 30
          },
          "quoteUrl": {
            "type": "string",
            "description": "URL to view or manage the quote.",
            "example": "https://test.onfastspring.com/popup-defaultB2B/account/order/quote/AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient information for the quote.",
            "properties": {
              "company": {
                "type": "string",
                "nullable": true,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "description": "Recipient's phone number.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID for the recipient, if any.",
                "example": null
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "properties": {
              "addressLine1": {
                "type": "string",
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "nullable": true,
                "description": "Second line of the address, if needed.",
                "example": null
              },
              "city": {
                "type": "string",
                "description": "City of the address.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "description": "Country code or name.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "description": "Postal or ZIP code for the address.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "description": "State or region.",
                "example": "California"
              }
            }
          },
          "siteId": {
            "type": "string",
            "description": "Identifier of the site associated with this quote.",
            "example": "aB0CdEFGHIjK"
          },
          "status": {
            "type": "string",
            "description": "Current status of the quote.",
            "example": "EXPIRED",
            "enum": [
              "OPEN",
              "CANCELED",
              "AWAITING_PAYMENT",
              "COMPLETED",
              "EXPIRED"
            ]
          },
          "statusHistory": {
            "type": "array",
            "description": "Array of objects describing status transitions.",
            "items": {
              "type": "object",
              "properties": {
                "statusUpdatedTo": {
                  "type": "string",
                  "description": "The status to which the quote was updated.",
                  "example": "EXPIRED"
                },
                "statusUpdatedByFullName": {
                  "type": "string",
                  "description": "Full name of the person (or system) updating the status.",
                  "example": "SYSTEM SYSTEM"
                },
                "statusUpdatedByEmail": {
                  "type": "string",
                  "description": "Email of the user (or system) who updated the status.",
                  "example": "SYSTEM"
                },
                "statusUpdatedOn": {
                  "type": "string",
                  "format": "date-time",
                  "description": "Timestamp of the status update.",
                  "example": "2024-10-10T00:04:24.046+00:00"
                }
              }
            }
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal in base currency before discounts and tax.",
            "example": 400
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$400.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "description": "Subtotal in the payout currency.",
            "example": 400
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal in the payout currency.",
            "example": "$400.00"
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects associated with the quote.",
            "items": {
              "type": "object",
              "description": "A custom key-value pair for tagging.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "tax": {
            "type": "number",
            "description": "Total tax amount in base currency.",
            "example": 0
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied, if any.",
            "example": 0
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied (e.g., TAX, VAT).",
            "example": "TAX"
          },
          "total": {
            "type": "number",
            "description": "Final total in base currency, after discounts/tax.",
            "example": 360
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted display of the total amount.",
            "example": "$360.00"
          },
          "totalInPayoutCurrency": {
            "type": "number",
            "description": "Total in the payout currency.",
            "example": 360
          },
          "totalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the total in payout currency.",
            "example": "$360.00"
          },
          "updated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was last updated.",
            "example": "2024-10-10T00:04:24.174+00:00"
          },
          "taxId": {
            "type": "string",
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "nullable": true,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          },
          "orderReference": {
            "type": "string",
            "nullable": true,
            "description": "Reference to an associated order, if any.",
            "example": "AB1234567-8910-11121"
          },
          "isGrossTax": {
            "type": "boolean",
            "description": "Indicates whether the quote uses gross tax.",
            "example": false
          },
          "invoiceId": {
            "type": "string",
            "nullable": true,
            "description": "Invoice ID associated with this quote, if any.",
            "example": "AB12CDE35FGHIJKLMN6OPQRSTUV"
          }
        }
      }
    }
  }
}
```

List all quotes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all quotes

Returns a list of your quotes.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Quotes",
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
      "name": "Quotes",
      "description": "Create, list, retrieve, update, and cancel quotes.\n"
    }
  ],
  "paths": {
    "/quotes": {
      "get": {
        "summary": "List all quotes",
        "tags": [
          "Quotes"
        ],
        "operationId": "getAllQuotes",
        "description": "Returns a list of your quotes.",
        "parameters": [
          {
            "name": "createdEmail",
            "in": "query",
            "required": false,
            "description": "Email address of the user who created the quote.",
            "schema": {
              "type": "string",
              "example": "jane.doe@example.com"
            }
          },
          {
            "name": "onlyQuoteId",
            "in": "query",
            "required": false,
            "description": "If set to `true`, only the quote ID is returned in the response.",
            "schema": {
              "type": "boolean",
              "example": true
            }
          },
          {
            "name": "statuses",
            "in": "query",
            "required": false,
            "description": "Filter results by specific status values.",
            "schema": {
              "type": "array",
              "items": {
                "type": "string",
                "enum": [
                  "OPEN",
                  "CANCELED",
                  "AWAITING_PAYMENT",
                  "COMPLETED",
                  "EXPIRED"
                ]
              },
              "example": [
                "OPEN",
                "CANCELED"
              ]
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ListAllQuotesResponse"
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
      "ListAllQuotesResponse": {
        "type": "object",
        "properties": {
          "_embedded": {
            "type": "object",
            "description": "Embedded wrapper containing the quotes array.",
            "properties": {
              "quotes": {
                "type": "array",
                "description": "Array of quote objects.",
                "items": {
                  "type": "object",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Unique identifier for the quote.",
                      "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S"
                    },
                    "buyerGenerated": {
                      "type": "boolean",
                      "description": "Indicates if the quote was created by the buyer.",
                      "example": false
                    },
                    "copyNotesToInvoice": {
                      "type": "boolean",
                      "description": "Determines whether notes should be copied to the invoice.",
                      "example": false
                    },
                    "coupon": {
                      "type": "string",
                      "nullable": true,
                      "description": "Coupon code applied to the quote, if any.",
                      "example": "TENOFF"
                    },
                    "created": {
                      "type": "string",
                      "format": "date-time",
                      "description": "Timestamp when the quote was created.",
                      "example": "2024-09-09T14:51:34.814+00:00"
                    },
                    "createdBy": {
                      "type": "object",
                      "description": "Information about the user who created this quote.",
                      "properties": {
                        "company": {
                          "type": "string",
                          "description": "Company name of the creator.",
                          "example": "Example Company"
                        },
                        "email": {
                          "type": "string",
                          "description": "Email address of the creator.",
                          "example": "jane.doe@example.com"
                        },
                        "first": {
                          "type": "string",
                          "description": "First name of the creator.",
                          "example": "Jane"
                        },
                        "last": {
                          "type": "string",
                          "description": "Last name of the creator.",
                          "example": "Doe"
                        },
                        "phone": {
                          "type": "string",
                          "nullable": true,
                          "description": "Phone number of the creator.",
                          "example": "5555551234"
                        },
                        "userId": {
                          "type": "string",
                          "nullable": true,
                          "description": "Unique user ID associated with the creator, if available.",
                          "example": "abCDEFGHijkLmNOpQr_S"
                        }
                      }
                    },
                    "currency": {
                      "type": "string",
                      "description": "Currency code used in the quote.",
                      "example": "USD"
                    },
                    "discount": {
                      "type": "number",
                      "description": "Discount amount in base currency.",
                      "example": 40
                    },
                    "discountDisplay": {
                      "type": "string",
                      "description": "Formatted display of the discount in base currency.",
                      "example": "$40.00"
                    },
                    "discountInPayoutCurrency": {
                      "type": "number",
                      "description": "Discount amount in the payout currency.",
                      "example": 40
                    },
                    "discountInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the discount in the payout currency.",
                      "example": "$40.00"
                    },
                    "expires": {
                      "type": "string",
                      "format": "date-time",
                      "description": "Timestamp when the quote expires.",
                      "example": "2024-10-09T14:51:34.814+00:00"
                    },
                    "expirationDateDays": {
                      "type": "integer",
                      "description": "Number of days until the quote expires.",
                      "example": 30
                    },
                    "fulfillmentTerm": {
                      "type": "string",
                      "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
                      "example": "ON_PAYMENT",
                      "enum": [
                        "ON_PAYMENT",
                        "ON_QUOTE_ACCEPTANCE"
                      ]
                    },
                    "items": {
                      "type": "array",
                      "description": "Array of items included in the quote.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "product": {
                            "type": "string",
                            "description": "Product identifier or name.",
                            "example": "add-on-subscription"
                          },
                          "customPrice": {
                            "type": "boolean",
                            "description": "Indicates if the price is custom.",
                            "example": false
                          },
                          "display": {
                            "type": "string",
                            "description": "Display name for the product.",
                            "example": "Add-on Subscription"
                          },
                          "image": {
                            "type": "string",
                            "nullable": true,
                            "description": "Image filename or URL, if applicable.",
                            "example": null
                          },
                          "intervalCount": {
                            "type": "string",
                            "nullable": true,
                            "description": "Number of intervals (e.g., for a subscription).",
                            "example": null
                          },
                          "period": {
                            "type": "string",
                            "nullable": true,
                            "description": "Billing period for the product (e.g., Monthly).",
                            "example": "Monthly"
                          },
                          "periodDays": {
                            "type": "string",
                            "nullable": true,
                            "description": "Number of days in the billing period.",
                            "example": null
                          },
                          "quantity": {
                            "type": "integer",
                            "description": "Quantity of the product.",
                            "example": 2
                          },
                          "renewIntoProducts": {
                            "type": "array",
                            "uniqueItems": true,
                            "description": "Array of product(s) into which this item can renew.",
                            "items": {
                              "type": "object",
                              "description": "Details of the product that this item renews into.",
                              "properties": {
                                "renewIntoProductId": {
                                  "type": "string",
                                  "description": "Product ID into which the item renews.",
                                  "example": "DG4UH337PVYDC33XEP3ZH3JHD"
                                },
                                "renewIntoPath": {
                                  "type": "string",
                                  "description": "Path or identifier for the renewing product.",
                                  "example": "video-subscription"
                                },
                                "renewIntoPrice": {
                                  "type": "number",
                                  "format": "float",
                                  "description": "Price for the renewing product.",
                                  "example": 9.99
                                },
                                "renewIntoPeriod": {
                                  "type": "string",
                                  "description": "Period for the renewing product (e.g., Weekly, Monthly).",
                                  "example": "Weekly"
                                },
                                "renewIntoLevel": {
                                  "type": "integer",
                                  "format": "int32",
                                  "description": "Level or tier for the renewal.",
                                  "example": 1
                                },
                                "renewIntoIntervalCount": {
                                  "type": "string",
                                  "description": "Interval count for the renewal (e.g., \"2\" months).",
                                  "example": "2"
                                },
                                "renewIntoUpcomingProduct": {
                                  "type": "string",
                                  "description": "Upcoming product name or handle after renewal.",
                                  "example": "audio-subscription"
                                }
                              }
                            }
                          },
                          "taxes": {
                            "type": "array",
                            "description": "Tax details for this item.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "taxValue": {
                                  "type": "number",
                                  "description": "Tax amount for this item.",
                                  "example": 0
                                },
                                "totalTaxable": {
                                  "type": "number",
                                  "description": "Total taxable amount for this item.",
                                  "example": 360
                                }
                              }
                            }
                          },
                          "trialDays": {
                            "type": "integer",
                            "nullable": true,
                            "description": "Number of trial days, if applicable.",
                            "example": null
                          },
                          "subscription": {
                            "type": "boolean",
                            "description": "Indicates if the item is a subscription.",
                            "example": true
                          },
                          "unitTrialPrice": {
                            "type": "number",
                            "description": "Trial price per unit, if applicable.",
                            "example": 0
                          },
                          "trialPriceWithoutTax": {
                            "type": "number",
                            "nullable": true,
                            "description": "Trial price without tax, if any.",
                            "example": null
                          },
                          "trialExtendedPrice": {
                            "type": "number",
                            "nullable": true,
                            "description": "Extended price for the trial, if any.",
                            "example": null
                          },
                          "trialExtendedPriceWithoutTax": {
                            "type": "number",
                            "nullable": true,
                            "description": "Extended trial price without tax.",
                            "example": null
                          },
                          "trialUnitTax": {
                            "type": "number",
                            "nullable": true,
                            "description": "Tax per unit during trial.",
                            "example": null
                          },
                          "trialExtendedTax": {
                            "type": "number",
                            "nullable": true,
                            "description": "Extended trial tax, if any.",
                            "example": null
                          },
                          "driver": {
                            "type": "string",
                            "nullable": true,
                            "description": "Driver or source reference, if any.",
                            "example": null
                          },
                          "type": {
                            "type": "string",
                            "nullable": true,
                            "description": "Additional type info.",
                            "example": null
                          },
                          "unitCouponDiscount": {
                            "type": "number",
                            "description": "Discount per unit from a coupon.",
                            "example": 0
                          },
                          "unitCouponDiscountDisplay": {
                            "type": "string",
                            "description": "Display format of coupon discount per unit.",
                            "example": "$0.00"
                          },
                          "unitCouponDiscountInPayoutCurrency": {
                            "type": "number",
                            "description": "Coupon discount in the payout currency.",
                            "example": 0
                          },
                          "unitCouponDiscountInPayoutCurrencyDisplay": {
                            "type": "string",
                            "description": "Display format of the coupon discount in payout currency.",
                            "example": "$0.00"
                          },
                          "unitDiscount": {
                            "type": "number",
                            "description": "Amount of discount per unit in base currency.",
                            "example": 20
                          },
                          "unitDiscountWithoutTax": {
                            "type": "number",
                            "description": "Per-unit discount before tax.",
                            "example": 20
                          },
                          "unitDiscountDisplay": {
                            "type": "string",
                            "description": "Formatted display of the discount per unit.",
                            "example": "$20.00"
                          },
                          "unitDiscountInPayoutCurrency": {
                            "type": "number",
                            "description": "Per-unit discount in payout currency.",
                            "example": 20
                          },
                          "unitDiscountInPayoutCurrencyDisplay": {
                            "type": "string",
                            "description": "Display format of the discount in payout currency.",
                            "example": "$20.00"
                          },
                          "unitListPrice": {
                            "type": "number",
                            "description": "List price per unit in the base currency.",
                            "example": 200
                          },
                          "unitListPriceDisplay": {
                            "type": "string",
                            "description": "Formatted display of the list price per unit in base currency.",
                            "example": "$200.00"
                          },
                          "unitListPriceInPayoutCurrency": {
                            "type": "number",
                            "description": "Per-unit list price in payout currency.",
                            "example": 200
                          },
                          "unitListPriceInPayoutCurrencyDisplay": {
                            "type": "string",
                            "description": "Formatted display of the list price in payout currency.",
                            "example": "$200.00"
                          },
                          "unitPrice": {
                            "type": "number",
                            "description": "Final price per unit in base currency.",
                            "example": 180
                          },
                          "unitPriceDisplay": {
                            "type": "string",
                            "description": "Formatted display of the final unit price.",
                            "example": "$180.00"
                          },
                          "unitPriceInPayoutCurrency": {
                            "type": "number",
                            "description": "Final price per unit in the payout currency.",
                            "example": 180
                          },
                          "unitPriceInPayoutCurrencyDisplay": {
                            "type": "string",
                            "description": "Formatted display of the final unit price in payout currency.",
                            "example": "$180.00"
                          },
                          "unitPriceWithoutTax": {
                            "type": "number",
                            "description": "Final unit price without tax.",
                            "example": 180
                          },
                          "unitPriceWithoutTaxDisplay": {
                            "type": "string",
                            "description": "Formatted display of the unit price without tax.",
                            "example": "$180.00"
                          },
                          "unitListPriceWithoutTax": {
                            "type": "number",
                            "description": "Original list price per unit before tax.",
                            "example": 200
                          },
                          "unitListPriceWithoutTaxDisplay": {
                            "type": "string",
                            "description": "Formatted display of the list price before tax.",
                            "example": "$200.00"
                          },
                          "grossModeOption": {
                            "type": "string",
                            "nullable": true,
                            "description": "Gross mode option, if applicable.",
                            "example": null
                          },
                          "taxMode": {
                            "type": "string",
                            "description": "Whether tax is NET or GROSS for this item.",
                            "example": "NET"
                          },
                          "priceDetail": {
                            "type": "object",
                            "description": "Detailed breakdown of the item’s pricing.",
                            "properties": {
                              "path": {
                                "type": "string",
                                "description": "Identifier or path for this item’s pricing context.",
                                "example": "add-on-subscription"
                              },
                              "quantity": {
                                "type": "integer",
                                "description": "Quantity used for this price detail.",
                                "example": 2
                              },
                              "plans": {
                                "type": "array",
                                "description": "Array of plan objects associated with pricing.",
                                "items": {
                                  "type": "object",
                                  "properties": {
                                    "path": {
                                      "type": "string",
                                      "description": "Plan’s product path.",
                                      "example": "add-on-subscription"
                                    },
                                    "interval": {
                                      "type": "string",
                                      "description": "Interval name (e.g., month, year).",
                                      "example": "month"
                                    },
                                    "intervalLength": {
                                      "type": "integer",
                                      "description": "Number of intervals per billing cycle.",
                                      "example": 1
                                    },
                                    "prices": {
                                      "type": "array",
                                      "description": "Detailed price lines for each plan.",
                                      "items": {
                                        "type": "object",
                                        "properties": {
                                          "path": {
                                            "type": "string",
                                            "description": "Product path for pricing context.",
                                            "example": "add-on-subscription"
                                          },
                                          "showUnitListPrice": {
                                            "type": "number",
                                            "description": "Displayed list price per unit.",
                                            "example": 200
                                          },
                                          "showUnitNetPrice": {
                                            "type": "number",
                                            "description": "Displayed net price per unit.",
                                            "example": 180
                                          },
                                          "showExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Displayed extended net price for all units.",
                                            "example": 360
                                          },
                                          "showExtendedListPrice": {
                                            "type": "number",
                                            "description": "Displayed extended list price.",
                                            "example": 400
                                          },
                                          "showUnitDiscountAmount": {
                                            "type": "number",
                                            "description": "Displayed discount amount per unit.",
                                            "example": 20
                                          },
                                          "showExtendedDiscountAmount": {
                                            "type": "number",
                                            "description": "Displayed total discount across all units.",
                                            "example": 40
                                          },
                                          "showExtendedTaxes": {
                                            "type": "number",
                                            "description": "Displayed total extended taxes.",
                                            "example": 0
                                          },
                                          "showTaxPercent": {
                                            "type": "number",
                                            "description": "Displayed tax percentage.",
                                            "example": 0
                                          },
                                          "subscriptionUnitListPrice": {
                                            "type": "number",
                                            "description": "Per-unit list price if this item is a subscription.",
                                            "example": 200
                                          },
                                          "subscriptionExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price for the subscription.",
                                            "example": 360
                                          },
                                          "subscriptionUnitDiscountAmount": {
                                            "type": "number",
                                            "description": "Discount amount per subscription unit.",
                                            "example": 20
                                          },
                                          "subscriptionTaxMode": {
                                            "type": "string",
                                            "description": "Tax mode for the subscription (NET or GROSS).",
                                            "example": "NET"
                                          },
                                          "unitListPrice": {
                                            "type": "number",
                                            "description": "Raw list price per unit in base currency.",
                                            "example": 200
                                          },
                                          "unitNetPrice": {
                                            "type": "number",
                                            "description": "Net price per unit in base currency.",
                                            "example": 180
                                          },
                                          "unitDiscountAmount": {
                                            "type": "number",
                                            "description": "Discount amount per unit in base currency.",
                                            "example": 20
                                          },
                                          "extendedListPrice": {
                                            "type": "number",
                                            "description": "Extended list price for all units.",
                                            "example": 400
                                          },
                                          "extendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price for all units.",
                                            "example": 360
                                          },
                                          "extendedDiscountAmount": {
                                            "type": "number",
                                            "description": "Extended total discount across all units.",
                                            "example": 40
                                          },
                                          "withTaxUnitListPrice": {
                                            "type": "number",
                                            "description": "Per-unit list price with tax included.",
                                            "example": 200
                                          },
                                          "withTaxUnitNetPrice": {
                                            "type": "number",
                                            "description": "Per-unit net price with tax included.",
                                            "example": 180
                                          },
                                          "withTaxUnitDiscountAmount": {
                                            "type": "number",
                                            "description": "Discount per unit, tax included.",
                                            "example": 20
                                          },
                                          "withTaxExtendedListPrice": {
                                            "type": "number",
                                            "description": "Extended list price with tax included.",
                                            "example": 400
                                          },
                                          "withTaxExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price with tax included.",
                                            "example": 360
                                          },
                                          "withTaxExtendedDiscountAmount": {
                                            "type": "number",
                                            "description": "Extended discount amount with tax included.",
                                            "example": 40
                                          },
                                          "withoutTaxUnitListPrice": {
                                            "type": "number",
                                            "description": "List price per unit without tax.",
                                            "example": 200
                                          },
                                          "withoutTaxUnitNetPrice": {
                                            "type": "number",
                                            "description": "Net price per unit without tax.",
                                            "example": 180
                                          },
                                          "withoutTaxUnitDiscountAmount": {
                                            "type": "number",
                                            "description": "Discount per unit without tax.",
                                            "example": 20
                                          },
                                          "withoutTaxExtendedListPrice": {
                                            "type": "number",
                                            "description": "Extended list price without tax.",
                                            "example": 400
                                          },
                                          "withoutTaxExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price without tax.",
                                            "example": 360
                                          },
                                          "withoutTaxExtendedDiscountAmount": {
                                            "type": "number",
                                            "description": "Extended discount amount without tax.",
                                            "example": 40
                                          },
                                          "discountPercent": {
                                            "type": "number",
                                            "description": "Percentage discount applied to this item.",
                                            "example": 10
                                          },
                                          "withTaxUnitProductDiscount": {
                                            "type": "number",
                                            "description": "Product discount per unit, tax included.",
                                            "example": 20
                                          },
                                          "withTaxExtendedProductDiscount": {
                                            "type": "number",
                                            "description": "Extended product discount with tax.",
                                            "example": 40
                                          },
                                          "withoutTaxUnitProductDiscount": {
                                            "type": "number",
                                            "description": "Product discount per unit without tax.",
                                            "example": 20
                                          },
                                          "withoutTaxExtendedProductDiscount": {
                                            "type": "number",
                                            "description": "Extended product discount without tax.",
                                            "example": 40
                                          },
                                          "productDiscountPercent": {
                                            "type": "number",
                                            "description": "Discount percent specific to the product.",
                                            "example": 0
                                          },
                                          "roundedWithoutTaxUnitNetPrice": {
                                            "type": "number",
                                            "description": "Rounded net unit price without tax.",
                                            "example": 180
                                          },
                                          "roundedWithoutTaxExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Rounded extended net price without tax.",
                                            "example": 360
                                          },
                                          "roundedExtendedTaxes": {
                                            "type": "number",
                                            "description": "Rounded extended taxes.",
                                            "example": 0
                                          },
                                          "roundedWithTaxExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Rounded net price with tax included.",
                                            "example": 360
                                          },
                                          "unitTaxes": {
                                            "type": "number",
                                            "description": "Tax per unit.",
                                            "example": 0
                                          },
                                          "extendedTaxes": {
                                            "type": "number",
                                            "description": "Extended total taxes.",
                                            "example": 0
                                          },
                                          "taxPercent": {
                                            "type": "number",
                                            "description": "Tax percentage.",
                                            "example": 0
                                          },
                                          "sourceListPrice": {
                                            "type": "number",
                                            "description": "Source or original list price.",
                                            "example": 200
                                          },
                                          "sourceCurrency": {
                                            "type": "string",
                                            "description": "Currency code used by the source price.",
                                            "example": "USD"
                                          },
                                          "lineNumber": {
                                            "type": "string",
                                            "description": "Unique identifier for this price line.",
                                            "example": "LNNS3BGRMB65JEK1FF3N9W9FEEKC"
                                          },
                                          "withholdingAmount": {
                                            "type": "number",
                                            "description": "Withholding amount in base currency.",
                                            "example": 0
                                          },
                                          "withholdingAmountUSD": {
                                            "type": "number",
                                            "description": "Withholding amount in USD, if different.",
                                            "example": 0
                                          },
                                          "rules": {
                                            "type": "array",
                                            "description": "Array of pricing rules applied.",
                                            "items": {
                                              "type": "object",
                                              "properties": {
                                                "name": {
                                                  "type": "string",
                                                  "description": "Name of the rule.",
                                                  "example": "DetermineCurrencyCode"
                                                },
                                                "reason": {
                                                  "type": "string",
                                                  "description": "Reason for applying this rule.",
                                                  "example": "Input currency"
                                                },
                                                "outputCurrency": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Currency determined by the rule.",
                                                  "example": "USD"
                                                },
                                                "trial": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Indicates if a trial was factored.",
                                                  "example": "false"
                                                },
                                                "endPrice": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Display of the final price after the rule.",
                                                  "example": "$200.00 USD"
                                                },
                                                "priceModel": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Pricing model (e.g., \"PER_UNIT\").",
                                                  "example": "PER_UNIT"
                                                },
                                                "startPrice": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Display of the price before the rule.",
                                                  "example": null
                                                },
                                                "description": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Brief explanation of the rule.",
                                                  "example": "Load price"
                                                },
                                                "operation": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Operation performed by the rule.",
                                                  "example": "+ 0.0000"
                                                },
                                                "operation2": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Secondary operation detail."
                                                },
                                                "taxMode": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "NET or GROSS tax mode indicated by the rule.",
                                                  "example": "NET"
                                                },
                                                "taxRate": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Tax rate used by the rule.",
                                                  "example": "0.0000"
                                                },
                                                "taxExempt": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Whether the item is tax exempt.",
                                                  "example": "false"
                                                },
                                                "unitPrice": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Unit price in the context of this rule.",
                                                  "example": "180.0000"
                                                },
                                                "unitTotal": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Unit total used by the rule."
                                                },
                                                "effectiveTaxRate": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Effective tax rate after the rule."
                                                },
                                                "quantity": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Quantity used by the rule.",
                                                  "example": "2"
                                                },
                                                "withTaxNetUnitRoundAmount": {
                                                  "type": "string",
                                                  "nullable": true,
                                                  "description": "Rounding amount for net unit price with tax."
                                                }
                                              }
                                            }
                                          },
                                          "effectiveTaxMode": {
                                            "type": "string",
                                            "description": "Final tax mode determined by the plan.",
                                            "example": "NET"
                                          },
                                          "dateLimitsEnabled": {
                                            "type": "boolean",
                                            "description": "Indicates if date limits were enforced.",
                                            "example": false
                                          },
                                          "discountTiers": {
                                            "type": "array",
                                            "description": "Array of discount tier objects for this plan.",
                                            "items": {
                                              "type": "object",
                                              "properties": {
                                                "min": {
                                                  "type": "integer",
                                                  "description": "Minimum quantity for this tier.",
                                                  "example": 1
                                                },
                                                "withTaxUnitNetPrice": {
                                                  "type": "number",
                                                  "description": "Unit net price with tax in this tier.",
                                                  "example": 180
                                                },
                                                "withoutTaxUnitNetPrice": {
                                                  "type": "number",
                                                  "description": "Unit net price without tax for this tier.",
                                                  "example": 180
                                                },
                                                "withoutTaxUnitDiscountAmount": {
                                                  "type": "number",
                                                  "description": "Per-unit discount amount without tax.",
                                                  "example": 20
                                                },
                                                "withTaxUnitDiscountAmount": {
                                                  "type": "number",
                                                  "description": "Per-unit discount amount with tax included.",
                                                  "example": 20
                                                },
                                                "discountPercent": {
                                                  "type": "number",
                                                  "description": "Discount percentage for this tier.",
                                                  "example": 10
                                                },
                                                "type": {
                                                  "type": "string",
                                                  "description": "Discount type (e.g., VOLUME_PERCENT_OFF).",
                                                  "example": "VOLUME_PERCENT_OFF"
                                                }
                                              }
                                            }
                                          },
                                          "discounts": {
                                            "type": "array",
                                            "description": "Discounts applied at the plan level.",
                                            "items": {
                                              "type": "object",
                                              "properties": {
                                                "discountType": {
                                                  "type": "string",
                                                  "description": "Type of discount (e.g., \"VOLUME_PERCENT_OFF\").",
                                                  "example": "VOLUME_PERCENT_OFF"
                                                },
                                                "discountDuration": {
                                                  "type": "integer",
                                                  "description": "How long (in intervals) this discount is valid.",
                                                  "example": 5
                                                },
                                                "discountPath": {
                                                  "type": "string",
                                                  "description": "Path or identifier for this discount.",
                                                  "example": "add-on-subscription"
                                                },
                                                "discountUnitAmount": {
                                                  "type": "number",
                                                  "description": "Discount amount per unit in base currency.",
                                                  "example": 20
                                                },
                                                "discountPercent": {
                                                  "type": "number",
                                                  "description": "Discount percentage applied.",
                                                  "example": 10
                                                }
                                              }
                                            }
                                          },
                                          "withTaxStoreCurrencyExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price (store currency) with tax.",
                                            "example": 360
                                          },
                                          "withTaxUSDExtendedNetPrice": {
                                            "type": "number",
                                            "description": "Extended net price in USD with tax.",
                                            "example": 360
                                          },
                                          "taxCode": {
                                            "type": "string",
                                            "description": "Code representing the tax classification.",
                                            "example": "DV021010"
                                          },
                                          "taxFormat": {
                                            "type": "string",
                                            "description": "Format or category of tax (e.g., \"DIGITAL_ONLY\").",
                                            "example": "DIGITAL_ONLY"
                                          },
                                          "pricingPlanRenew": {
                                            "type": "string",
                                            "description": "Renewal policy for the pricing plan (e.g., \"auto\").",
                                            "example": "auto"
                                          },
                                          "customPrice": {
                                            "type": "boolean",
                                            "description": "Indicates if a custom price was used.",
                                            "example": false
                                          },
                                          "paidTrial": {
                                            "type": "boolean",
                                            "description": "Indicates if the trial period is paid.",
                                            "example": false
                                          },
                                          "paymentRequired": {
                                            "type": "boolean",
                                            "description": "Whether payment is required for the plan.",
                                            "example": true
                                          },
                                          "reactivationEnabled": {
                                            "type": "boolean",
                                            "description": "Indicates if reactivation is allowed.",
                                            "example": false
                                          },
                                          "reactivationExpirationDays": {
                                            "type": "integer",
                                            "description": "Days before a reactivation link expires.",
                                            "example": 0
                                          },
                                          "taxExemptedUnitAmount": {
                                            "type": "number",
                                            "description": "Per-unit amount exempted from tax.",
                                            "example": 0
                                          },
                                          "taxExemptedExtendedAmount": {
                                            "type": "number",
                                            "description": "Extended total amount exempted from tax.",
                                            "example": 0
                                          },
                                          "withoutTaxExemptionUnitNetPrice": {
                                            "type": "number",
                                            "description": "Net price per unit when not tax-exempt.",
                                            "example": 180
                                          },
                                          "withoutTaxExemptionUnitListPrice": {
                                            "type": "number",
                                            "description": "List price per unit when not tax-exempt.",
                                            "example": 200
                                          },
                                          "quantity": {
                                            "type": "integer",
                                            "description": "Quantity used in pricing calculations.",
                                            "example": 2
                                          },
                                          "discountPath": {
                                            "type": "string",
                                            "description": "Path for applying the discount, if any.",
                                            "example": "add-on-subscription"
                                          }
                                        }
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          },
                          "quantityBehavior": {
                            "type": "string",
                            "description": "Determines how quantity changes are handled.",
                            "example": "allow"
                          },
                          "quantityDefault": {
                            "type": "integer",
                            "description": "Default quantity if none is specified.",
                            "example": 1
                          },
                          "discountDuration": {
                            "type": "integer",
                            "description": "Number of intervals the discount remains valid.",
                            "example": 5
                          },
                          "cancellationChoice": {
                            "type": "string",
                            "description": "Cancellation policy (e.g., AFTER_LAST_NOTIFICATION).",
                            "example": "AFTER_LAST_NOTIFICATION"
                          },
                          "startNewTerm": {
                            "type": "boolean",
                            "description": "Whether a new subscription term should begin.",
                            "example": false
                          },
                          "subscriptionCancelDisabled": {
                            "type": "boolean",
                            "description": "Indicates if subscription cancellation is disabled.",
                            "example": false
                          },
                          "notifications": {
                            "type": "array",
                            "description": "Notification rules for different events.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "notificationType": {
                                  "type": "string",
                                  "description": "Type of notification (e.g., PAYMENT_REMINDER).",
                                  "example": "TRIAL_CONVERSION_REMINDER"
                                },
                                "enabled": {
                                  "type": "boolean",
                                  "description": "Indicates if the notification is enabled.",
                                  "example": false
                                },
                                "intervals": {
                                  "type": "array",
                                  "description": "Interval definitions for sending notifications.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "intervalUnit": {
                                        "type": "string",
                                        "description": "Unit of interval (day, week, etc.).",
                                        "example": "day"
                                      },
                                      "intervalLength": {
                                        "type": "integer",
                                        "description": "Length of each interval.",
                                        "example": 3
                                      },
                                      "intervalCount": {
                                        "type": "integer",
                                        "nullable": true,
                                        "description": "Number of times to repeat, if applicable."
                                      }
                                    }
                                  }
                                },
                                "firstIntervalUnit": {
                                  "type": "string",
                                  "description": "Unit for the first notification interval.",
                                  "example": "day"
                                },
                                "firstIntervalLength": {
                                  "type": "integer",
                                  "description": "Length of the first interval.",
                                  "example": 3
                                },
                                "firstIntervalCount": {
                                  "type": "integer",
                                  "nullable": true,
                                  "description": "How many times the first interval repeats."
                                }
                              }
                            }
                          },
                          "productDescriptions": {
                            "type": "object",
                            "description": "Localized display and summary of the product.",
                            "properties": {
                              "display": {
                                "type": "object",
                                "description": "Localized display text.",
                                "properties": {
                                  "en": {
                                    "type": "string",
                                    "example": "Add-on Subscription"
                                  }
                                }
                              },
                              "summary": {
                                "type": "object",
                                "description": "Localized summary text.",
                                "properties": {
                                  "en": {
                                    "type": "string",
                                    "example": "This is my product summary"
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    },
                    "name": {
                      "type": "string",
                      "description": "Name or label for the quote.",
                      "example": "Name of the quote"
                    },
                    "notes": {
                      "type": "string",
                      "nullable": true,
                      "description": "Additional notes for the quote.",
                      "example": null
                    },
                    "netTermsDays": {
                      "type": "integer",
                      "description": "Number of days for net payment terms.",
                      "example": 30
                    },
                    "quoteUrl": {
                      "type": "string",
                      "description": "URL to view or manage the quote.",
                      "example": "https://test.onfastspring.com/popup-defaultB2B/account/order/quote/AB1CD23EFG45H6IJKLMNOPQ78R9S"
                    },
                    "recipient": {
                      "type": "object",
                      "description": "Recipient information for the quote.",
                      "properties": {
                        "company": {
                          "type": "string",
                          "nullable": true,
                          "description": "Recipient's company name, if any.",
                          "example": "Example Company"
                        },
                        "email": {
                          "type": "string",
                          "description": "Recipient's email address.",
                          "example": "jane.doe@example.com"
                        },
                        "first": {
                          "type": "string",
                          "description": "Recipient's first name.",
                          "example": "Jane"
                        },
                        "last": {
                          "type": "string",
                          "description": "Recipient's last name.",
                          "example": "Doe"
                        },
                        "phone": {
                          "type": "string",
                          "description": "Recipient's phone number.",
                          "example": "5555551234"
                        },
                        "userId": {
                          "type": "string",
                          "nullable": true,
                          "description": "Unique user ID for the recipient, if any.",
                          "example": null
                        }
                      }
                    },
                    "recipientAddress": {
                      "type": "object",
                      "description": "Address of the quote recipient.",
                      "properties": {
                        "addressLine1": {
                          "type": "string",
                          "description": "First line of the address.",
                          "example": "123 Main Street"
                        },
                        "addressLine2": {
                          "type": "string",
                          "nullable": true,
                          "description": "Second line of the address, if needed.",
                          "example": null
                        },
                        "city": {
                          "type": "string",
                          "description": "City of the address.",
                          "example": "Example City"
                        },
                        "country": {
                          "type": "string",
                          "description": "Country code or name.",
                          "example": "US"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "Postal or ZIP code for the address.",
                          "example": "12345"
                        },
                        "region": {
                          "type": "string",
                          "description": "State or region.",
                          "example": "California"
                        }
                      }
                    },
                    "siteId": {
                      "type": "string",
                      "description": "Identifier of the site associated with this quote.",
                      "example": "aB0CdEFGHIjK"
                    },
                    "status": {
                      "type": "string",
                      "description": "Current status of the quote.",
                      "example": "EXPIRED",
                      "enum": [
                        "OPEN",
                        "CANCELED",
                        "AWAITING_PAYMENT",
                        "COMPLETED",
                        "EXPIRED"
                      ]
                    },
                    "statusHistory": {
                      "type": "array",
                      "description": "Array of objects describing status transitions.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "statusUpdatedTo": {
                            "type": "string",
                            "description": "The status to which the quote was updated.",
                            "example": "EXPIRED"
                          },
                          "statusUpdatedByFullName": {
                            "type": "string",
                            "description": "Full name of the person (or system) updating the status.",
                            "example": "SYSTEM SYSTEM"
                          },
                          "statusUpdatedByEmail": {
                            "type": "string",
                            "description": "Email of the user (or system) who updated the status.",
                            "example": "SYSTEM"
                          },
                          "statusUpdatedOn": {
                            "type": "string",
                            "format": "date-time",
                            "description": "Timestamp of the status update.",
                            "example": "2024-10-10T00:04:24.046+00:00"
                          }
                        }
                      }
                    },
                    "subtotal": {
                      "type": "number",
                      "description": "Subtotal in base currency before discounts and tax.",
                      "example": 400
                    },
                    "subtotalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal.",
                      "example": "$400.00"
                    },
                    "subtotalInPayoutCurrency": {
                      "type": "number",
                      "description": "Subtotal in the payout currency.",
                      "example": 400
                    },
                    "subtotalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal in the payout currency.",
                      "example": "$400.00"
                    },
                    "tags": {
                      "type": "array",
                      "description": "Array of tag objects associated with the quote.",
                      "items": {
                        "type": "object",
                        "description": "A custom key-value pair for tagging.",
                        "properties": {
                          "key": {
                            "type": "string",
                            "description": "The tag key or name.",
                            "example": "tag-key"
                          },
                          "value": {
                            "type": "string",
                            "description": "The tag value associated with this key.",
                            "example": "Tag Value"
                          }
                        }
                      }
                    },
                    "tax": {
                      "type": "number",
                      "description": "Total tax amount in base currency.",
                      "example": 0
                    },
                    "taxRate": {
                      "type": "number",
                      "description": "Tax rate applied, if any.",
                      "example": 0
                    },
                    "taxType": {
                      "type": "string",
                      "description": "Type of tax applied (e.g., TAX, VAT).",
                      "example": "TAX"
                    },
                    "total": {
                      "type": "number",
                      "description": "Final total in base currency, after discounts/tax.",
                      "example": 360
                    },
                    "totalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total amount.",
                      "example": "$360.00"
                    },
                    "totalInPayoutCurrency": {
                      "type": "number",
                      "description": "Total in the payout currency.",
                      "example": 360
                    },
                    "totalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total in payout currency.",
                      "example": "$360.00"
                    },
                    "updated": {
                      "type": "string",
                      "format": "date-time",
                      "description": "Timestamp when the quote was last updated.",
                      "example": "2024-10-10T00:04:24.174+00:00"
                    },
                    "taxId": {
                      "type": "string",
                      "nullable": true,
                      "description": "Tax ID associated with the quote, if any.",
                      "example": "BE09999999XX"
                    },
                    "source": {
                      "type": "string",
                      "description": "Source from which the quote was generated (e.g., MANAGER).",
                      "example": "MANAGER"
                    },
                    "sourceIP": {
                      "type": "string",
                      "nullable": true,
                      "description": "IP address from which the quote was created or updated.",
                      "example": "198.51.100.45"
                    },
                    "orderReference": {
                      "type": "string",
                      "nullable": true,
                      "description": "Reference to an associated order, if any.",
                      "example": "AB1234567-8910-11121"
                    },
                    "isGrossTax": {
                      "type": "boolean",
                      "description": "Indicates whether the quote uses gross tax.",
                      "example": false
                    },
                    "invoiceId": {
                      "type": "string",
                      "nullable": true,
                      "description": "Invoice ID associated with this quote, if any.",
                      "example": "AB12CDE35FGHIJKLMN6OPQRSTUV"
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

Retrieve a quote

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a quote

Retrieves the details of an existing quote with the given `quote_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Quotes",
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
      "name": "Quotes",
      "description": "Create, list, retrieve, update, and cancel quotes.\n"
    }
  ],
  "paths": {
    "/quotes/{quote_id}": {
      "get": {
        "summary": "Retrieve a quote",
        "tags": [
          "Quotes"
        ],
        "operationId": "getQuoteById",
        "description": "Retrieves the details of an existing quote with the given `quote_id`.",
        "parameters": [
          {
            "name": "quote_id",
            "in": "path",
            "required": true,
            "description": "A unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S",
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
                  "$ref": "#/components/schemas/QuoteResponse"
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
      "QuoteResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "buyerGenerated": {
            "type": "boolean",
            "description": "Indicates if the quote was created by the buyer.",
            "example": false
          },
          "copyNotesToInvoice": {
            "type": "boolean",
            "description": "Determines whether notes should be copied to the invoice.",
            "example": false
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was created.",
            "example": "2024-09-09T14:51:34.814+00:00"
          },
          "createdBy": {
            "type": "object",
            "description": "Information about the user who created this quote.",
            "properties": {
              "company": {
                "type": "string",
                "description": "Company name of the creator.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Email address of the creator.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "First name of the creator.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Last name of the creator.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "nullable": true,
                "description": "Phone number of the creator.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID associated with the creator, if available.",
                "example": "abCDEFGHijkLmNOpQr_S"
              }
            }
          },
          "currency": {
            "type": "string",
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount in base currency.",
            "example": 40
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in base currency.",
            "example": "$40.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "description": "Discount amount in the payout currency.",
            "example": 40
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in the payout currency.",
            "example": "$40.00"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote expires.",
            "example": "2024-10-09T14:51:34.814+00:00"
          },
          "expirationDateDays": {
            "type": "integer",
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "example": "ON_PAYMENT",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ]
          },
          "items": {
            "type": "array",
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "customPrice": {
                  "type": "boolean",
                  "description": "Indicates if the price is custom.",
                  "example": false
                },
                "display": {
                  "type": "string",
                  "description": "Display name for the product.",
                  "example": "Add-on Subscription"
                },
                "image": {
                  "type": "string",
                  "nullable": true,
                  "description": "Image filename or URL, if applicable.",
                  "example": null
                },
                "intervalCount": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of intervals (e.g., for a subscription).",
                  "example": null
                },
                "period": {
                  "type": "string",
                  "nullable": true,
                  "description": "Billing period for the product (e.g., Monthly).",
                  "example": "Monthly"
                },
                "periodDays": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of days in the billing period.",
                  "example": null
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "renewIntoProducts": {
                  "type": "array",
                  "uniqueItems": true,
                  "description": "Array of product(s) into which this item can renew.",
                  "items": {
                    "type": "object",
                    "description": "Details of the product that this item renews into.",
                    "properties": {
                      "renewIntoProductId": {
                        "type": "string",
                        "description": "Product ID into which the item renews.",
                        "example": "DG4UH337PVYDC33XEP3ZH3JHD"
                      },
                      "renewIntoPath": {
                        "type": "string",
                        "description": "Path or identifier for the renewing product.",
                        "example": "video-subscription"
                      },
                      "renewIntoPrice": {
                        "type": "number",
                        "format": "float",
                        "description": "Price for the renewing product.",
                        "example": 9.99
                      },
                      "renewIntoPeriod": {
                        "type": "string",
                        "description": "Period for the renewing product (e.g., Weekly, Monthly).",
                        "example": "Weekly"
                      },
                      "renewIntoLevel": {
                        "type": "integer",
                        "format": "int32",
                        "description": "Level or tier for the renewal.",
                        "example": 1
                      },
                      "renewIntoIntervalCount": {
                        "type": "string",
                        "description": "Interval count for the renewal (e.g., \"2\" months).",
                        "example": "2"
                      },
                      "renewIntoUpcomingProduct": {
                        "type": "string",
                        "description": "Upcoming product name or handle after renewal.",
                        "example": "audio-subscription"
                      }
                    }
                  }
                },
                "taxes": {
                  "type": "array",
                  "description": "Tax details for this item.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "taxValue": {
                        "type": "number",
                        "description": "Tax amount for this item.",
                        "example": 0
                      },
                      "totalTaxable": {
                        "type": "number",
                        "description": "Total taxable amount for this item.",
                        "example": 360
                      }
                    }
                  }
                },
                "trialDays": {
                  "type": "integer",
                  "nullable": true,
                  "description": "Number of trial days, if applicable.",
                  "example": null
                },
                "subscription": {
                  "type": "boolean",
                  "description": "Indicates if the item is a subscription.",
                  "example": true
                },
                "unitTrialPrice": {
                  "type": "number",
                  "description": "Trial price per unit, if applicable.",
                  "example": 0
                },
                "trialPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Trial price without tax, if any.",
                  "example": null
                },
                "trialExtendedPrice": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended price for the trial, if any.",
                  "example": null
                },
                "trialExtendedPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial price without tax.",
                  "example": null
                },
                "trialUnitTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Tax per unit during trial.",
                  "example": null
                },
                "trialExtendedTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial tax, if any.",
                  "example": null
                },
                "driver": {
                  "type": "string",
                  "nullable": true,
                  "description": "Driver or source reference, if any.",
                  "example": null
                },
                "type": {
                  "type": "string",
                  "nullable": true,
                  "description": "Additional type info.",
                  "example": null
                },
                "unitCouponDiscount": {
                  "type": "number",
                  "description": "Discount per unit from a coupon.",
                  "example": 0
                },
                "unitCouponDiscountDisplay": {
                  "type": "string",
                  "description": "Display format of coupon discount per unit.",
                  "example": "$0.00"
                },
                "unitCouponDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Coupon discount in the payout currency.",
                  "example": 0
                },
                "unitCouponDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the coupon discount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "description": "Amount of discount per unit in base currency.",
                  "example": 20
                },
                "unitDiscountWithoutTax": {
                  "type": "number",
                  "description": "Per-unit discount before tax.",
                  "example": 20
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$20.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit discount in payout currency.",
                  "example": 20
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the discount in payout currency.",
                  "example": "$20.00"
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in the base currency.",
                  "example": 200
                },
                "unitListPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price per unit in base currency.",
                  "example": "$200.00"
                },
                "unitListPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit list price in payout currency.",
                  "example": 200
                },
                "unitListPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price in payout currency.",
                  "example": "$200.00"
                },
                "unitPrice": {
                  "type": "number",
                  "description": "Final price per unit in base currency.",
                  "example": 180
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price.",
                  "example": "$180.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Final price per unit in the payout currency.",
                  "example": 180
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price in payout currency.",
                  "example": "$180.00"
                },
                "unitPriceWithoutTax": {
                  "type": "number",
                  "description": "Final unit price without tax.",
                  "example": 180
                },
                "unitPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the unit price without tax.",
                  "example": "$180.00"
                },
                "unitListPriceWithoutTax": {
                  "type": "number",
                  "description": "Original list price per unit before tax.",
                  "example": 200
                },
                "unitListPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price before tax.",
                  "example": "$200.00"
                },
                "grossModeOption": {
                  "type": "string",
                  "nullable": true,
                  "description": "Gross mode option, if applicable.",
                  "example": null
                },
                "taxMode": {
                  "type": "string",
                  "description": "Whether tax is NET or GROSS for this item.",
                  "example": "NET"
                },
                "priceDetail": {
                  "type": "object",
                  "description": "Detailed breakdown of the item’s pricing.",
                  "properties": {
                    "path": {
                      "type": "string",
                      "description": "Identifier or path for this item’s pricing context.",
                      "example": "add-on-subscription"
                    },
                    "quantity": {
                      "type": "integer",
                      "description": "Quantity used for this price detail.",
                      "example": 2
                    },
                    "plans": {
                      "type": "array",
                      "description": "Array of plan objects associated with pricing.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "path": {
                            "type": "string",
                            "description": "Plan’s product path.",
                            "example": "add-on-subscription"
                          },
                          "interval": {
                            "type": "string",
                            "description": "Interval name (e.g., month, year).",
                            "example": "month"
                          },
                          "intervalLength": {
                            "type": "integer",
                            "description": "Number of intervals per billing cycle.",
                            "example": 1
                          },
                          "prices": {
                            "type": "array",
                            "description": "Detailed price lines for each plan.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "path": {
                                  "type": "string",
                                  "description": "Product path for pricing context.",
                                  "example": "add-on-subscription"
                                },
                                "showUnitListPrice": {
                                  "type": "number",
                                  "description": "Displayed list price per unit.",
                                  "example": 200
                                },
                                "showUnitNetPrice": {
                                  "type": "number",
                                  "description": "Displayed net price per unit.",
                                  "example": 180
                                },
                                "showExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Displayed extended net price for all units.",
                                  "example": 360
                                },
                                "showExtendedListPrice": {
                                  "type": "number",
                                  "description": "Displayed extended list price.",
                                  "example": 400
                                },
                                "showUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed discount amount per unit.",
                                  "example": 20
                                },
                                "showExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed total discount across all units.",
                                  "example": 40
                                },
                                "showExtendedTaxes": {
                                  "type": "number",
                                  "description": "Displayed total extended taxes.",
                                  "example": 0
                                },
                                "showTaxPercent": {
                                  "type": "number",
                                  "description": "Displayed tax percentage.",
                                  "example": 0
                                },
                                "subscriptionUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price if this item is a subscription.",
                                  "example": 200
                                },
                                "subscriptionExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for the subscription.",
                                  "example": 360
                                },
                                "subscriptionUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per subscription unit.",
                                  "example": 20
                                },
                                "subscriptionTaxMode": {
                                  "type": "string",
                                  "description": "Tax mode for the subscription (NET or GROSS).",
                                  "example": "NET"
                                },
                                "unitListPrice": {
                                  "type": "number",
                                  "description": "Raw list price per unit in base currency.",
                                  "example": 200
                                },
                                "unitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit in base currency.",
                                  "example": 180
                                },
                                "unitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per unit in base currency.",
                                  "example": 20
                                },
                                "extendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price for all units.",
                                  "example": 400
                                },
                                "extendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for all units.",
                                  "example": 360
                                },
                                "extendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended total discount across all units.",
                                  "example": 40
                                },
                                "withTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price with tax included.",
                                  "example": 200
                                },
                                "withTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Per-unit net price with tax included.",
                                  "example": 180
                                },
                                "withTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price with tax included.",
                                  "example": 400
                                },
                                "withTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price with tax included.",
                                  "example": 360
                                },
                                "withTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount with tax included.",
                                  "example": 40
                                },
                                "withoutTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit without tax.",
                                  "example": 200
                                },
                                "withoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit without tax.",
                                  "example": 180
                                },
                                "withoutTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price without tax.",
                                  "example": 400
                                },
                                "withoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price without tax.",
                                  "example": 360
                                },
                                "withoutTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount without tax.",
                                  "example": 40
                                },
                                "discountPercent": {
                                  "type": "number",
                                  "description": "Percentage discount applied to this item.",
                                  "example": 10
                                },
                                "withTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount with tax.",
                                  "example": 40
                                },
                                "withoutTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount without tax.",
                                  "example": 40
                                },
                                "productDiscountPercent": {
                                  "type": "number",
                                  "description": "Discount percent specific to the product.",
                                  "example": 0
                                },
                                "roundedWithoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net unit price without tax.",
                                  "example": 180
                                },
                                "roundedWithoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded extended net price without tax.",
                                  "example": 360
                                },
                                "roundedExtendedTaxes": {
                                  "type": "number",
                                  "description": "Rounded extended taxes.",
                                  "example": 0
                                },
                                "roundedWithTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net price with tax included.",
                                  "example": 360
                                },
                                "unitTaxes": {
                                  "type": "number",
                                  "description": "Tax per unit.",
                                  "example": 0
                                },
                                "extendedTaxes": {
                                  "type": "number",
                                  "description": "Extended total taxes.",
                                  "example": 0
                                },
                                "taxPercent": {
                                  "type": "number",
                                  "description": "Tax percentage.",
                                  "example": 0
                                },
                                "sourceListPrice": {
                                  "type": "number",
                                  "description": "Source or original list price.",
                                  "example": 200
                                },
                                "sourceCurrency": {
                                  "type": "string",
                                  "description": "Currency code used by the source price.",
                                  "example": "USD"
                                },
                                "lineNumber": {
                                  "type": "string",
                                  "description": "Unique identifier for this price line.",
                                  "example": "LNNS3BGRMB65JEK1FF3N9W9FEEKC"
                                },
                                "withholdingAmount": {
                                  "type": "number",
                                  "description": "Withholding amount in base currency.",
                                  "example": 0
                                },
                                "withholdingAmountUSD": {
                                  "type": "number",
                                  "description": "Withholding amount in USD, if different.",
                                  "example": 0
                                },
                                "rules": {
                                  "type": "array",
                                  "description": "Array of pricing rules applied.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "name": {
                                        "type": "string",
                                        "description": "Name of the rule.",
                                        "example": "DetermineCurrencyCode"
                                      },
                                      "reason": {
                                        "type": "string",
                                        "description": "Reason for applying this rule.",
                                        "example": "Input currency"
                                      },
                                      "outputCurrency": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Currency determined by the rule.",
                                        "example": "USD"
                                      },
                                      "trial": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Indicates if a trial was factored.",
                                        "example": "false"
                                      },
                                      "endPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the final price after the rule.",
                                        "example": "$200.00 USD"
                                      },
                                      "priceModel": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Pricing model (e.g., \"PER_UNIT\").",
                                        "example": "PER_UNIT"
                                      },
                                      "startPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the price before the rule.",
                                        "example": null
                                      },
                                      "description": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Brief explanation of the rule.",
                                        "example": "Load price"
                                      },
                                      "operation": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Operation performed by the rule.",
                                        "example": "+ 0.0000"
                                      },
                                      "operation2": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Secondary operation detail."
                                      },
                                      "taxMode": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "NET or GROSS tax mode indicated by the rule.",
                                        "example": "NET"
                                      },
                                      "taxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Tax rate used by the rule.",
                                        "example": "0.0000"
                                      },
                                      "taxExempt": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Whether the item is tax exempt.",
                                        "example": "false"
                                      },
                                      "unitPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit price in the context of this rule.",
                                        "example": "180.0000"
                                      },
                                      "unitTotal": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit total used by the rule."
                                      },
                                      "effectiveTaxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Effective tax rate after the rule."
                                      },
                                      "quantity": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Quantity used by the rule.",
                                        "example": "2"
                                      },
                                      "withTaxNetUnitRoundAmount": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Rounding amount for net unit price with tax."
                                      }
                                    }
                                  }
                                },
                                "effectiveTaxMode": {
                                  "type": "string",
                                  "description": "Final tax mode determined by the plan.",
                                  "example": "NET"
                                },
                                "dateLimitsEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if date limits were enforced.",
                                  "example": false
                                },
                                "discountTiers": {
                                  "type": "array",
                                  "description": "Array of discount tier objects for this plan.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "min": {
                                        "type": "integer",
                                        "description": "Minimum quantity for this tier.",
                                        "example": 1
                                      },
                                      "withTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price with tax in this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price without tax for this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount without tax.",
                                        "example": 20
                                      },
                                      "withTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount with tax included.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage for this tier.",
                                        "example": 10
                                      },
                                      "type": {
                                        "type": "string",
                                        "description": "Discount type (e.g., VOLUME_PERCENT_OFF).",
                                        "example": "VOLUME_PERCENT_OFF"
                                      }
                                    }
                                  }
                                },
                                "discounts": {
                                  "type": "array",
                                  "description": "Discounts applied at the plan level.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "discountType": {
                                        "type": "string",
                                        "description": "Type of discount (e.g., \"VOLUME_PERCENT_OFF\").",
                                        "example": "VOLUME_PERCENT_OFF"
                                      },
                                      "discountDuration": {
                                        "type": "integer",
                                        "description": "How long (in intervals) this discount is valid.",
                                        "example": 5
                                      },
                                      "discountPath": {
                                        "type": "string",
                                        "description": "Path or identifier for this discount.",
                                        "example": "add-on-subscription"
                                      },
                                      "discountUnitAmount": {
                                        "type": "number",
                                        "description": "Discount amount per unit in base currency.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage applied.",
                                        "example": 10
                                      }
                                    }
                                  }
                                },
                                "withTaxStoreCurrencyExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price (store currency) with tax.",
                                  "example": 360
                                },
                                "withTaxUSDExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price in USD with tax.",
                                  "example": 360
                                },
                                "taxCode": {
                                  "type": "string",
                                  "description": "Code representing the tax classification.",
                                  "example": "DV021010"
                                },
                                "taxFormat": {
                                  "type": "string",
                                  "description": "Format or category of tax (e.g., \"DIGITAL_ONLY\").",
                                  "example": "DIGITAL_ONLY"
                                },
                                "pricingPlanRenew": {
                                  "type": "string",
                                  "description": "Renewal policy for the pricing plan (e.g., \"auto\").",
                                  "example": "auto"
                                },
                                "customPrice": {
                                  "type": "boolean",
                                  "description": "Indicates if a custom price was used.",
                                  "example": false
                                },
                                "paidTrial": {
                                  "type": "boolean",
                                  "description": "Indicates if the trial period is paid.",
                                  "example": false
                                },
                                "paymentRequired": {
                                  "type": "boolean",
                                  "description": "Whether payment is required for the plan.",
                                  "example": true
                                },
                                "reactivationEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if reactivation is allowed.",
                                  "example": false
                                },
                                "reactivationExpirationDays": {
                                  "type": "integer",
                                  "description": "Days before a reactivation link expires.",
                                  "example": 0
                                },
                                "taxExemptedUnitAmount": {
                                  "type": "number",
                                  "description": "Per-unit amount exempted from tax.",
                                  "example": 0
                                },
                                "taxExemptedExtendedAmount": {
                                  "type": "number",
                                  "description": "Extended total amount exempted from tax.",
                                  "example": 0
                                },
                                "withoutTaxExemptionUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit when not tax-exempt.",
                                  "example": 180
                                },
                                "withoutTaxExemptionUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit when not tax-exempt.",
                                  "example": 200
                                },
                                "quantity": {
                                  "type": "integer",
                                  "description": "Quantity used in pricing calculations.",
                                  "example": 2
                                },
                                "discountPath": {
                                  "type": "string",
                                  "description": "Path for applying the discount, if any.",
                                  "example": "add-on-subscription"
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                },
                "quantityBehavior": {
                  "type": "string",
                  "description": "Determines how quantity changes are handled.",
                  "example": "allow"
                },
                "quantityDefault": {
                  "type": "integer",
                  "description": "Default quantity if none is specified.",
                  "example": 1
                },
                "discountDuration": {
                  "type": "integer",
                  "description": "Number of intervals the discount remains valid.",
                  "example": 5
                },
                "cancellationChoice": {
                  "type": "string",
                  "description": "Cancellation policy (e.g., AFTER_LAST_NOTIFICATION).",
                  "example": "AFTER_LAST_NOTIFICATION"
                },
                "startNewTerm": {
                  "type": "boolean",
                  "description": "Whether a new subscription term should begin.",
                  "example": false
                },
                "subscriptionCancelDisabled": {
                  "type": "boolean",
                  "description": "Indicates if subscription cancellation is disabled.",
                  "example": false
                },
                "notifications": {
                  "type": "array",
                  "description": "Notification rules for different events.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "notificationType": {
                        "type": "string",
                        "description": "Type of notification (e.g., PAYMENT_REMINDER).",
                        "example": "TRIAL_CONVERSION_REMINDER"
                      },
                      "enabled": {
                        "type": "boolean",
                        "description": "Indicates if the notification is enabled.",
                        "example": false
                      },
                      "intervals": {
                        "type": "array",
                        "description": "Interval definitions for sending notifications.",
                        "items": {
                          "type": "object",
                          "properties": {
                            "intervalUnit": {
                              "type": "string",
                              "description": "Unit of interval (day, week, etc.).",
                              "example": "day"
                            },
                            "intervalLength": {
                              "type": "integer",
                              "description": "Length of each interval.",
                              "example": 3
                            },
                            "intervalCount": {
                              "type": "integer",
                              "nullable": true,
                              "description": "Number of times to repeat, if applicable."
                            }
                          }
                        }
                      },
                      "firstIntervalUnit": {
                        "type": "string",
                        "description": "Unit for the first notification interval.",
                        "example": "day"
                      },
                      "firstIntervalLength": {
                        "type": "integer",
                        "description": "Length of the first interval.",
                        "example": 3
                      },
                      "firstIntervalCount": {
                        "type": "integer",
                        "nullable": true,
                        "description": "How many times the first interval repeats."
                      }
                    }
                  }
                },
                "productDescriptions": {
                  "type": "object",
                  "description": "Localized display and summary of the product.",
                  "properties": {
                    "display": {
                      "type": "object",
                      "description": "Localized display text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "Add-on Subscription"
                        }
                      }
                    },
                    "summary": {
                      "type": "object",
                      "description": "Localized summary text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "This is my product summary"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "Name or label for the quote.",
            "example": "Name of the quote"
          },
          "notes": {
            "type": "string",
            "nullable": true,
            "description": "Additional notes for the quote.",
            "example": null
          },
          "netTermsDays": {
            "type": "integer",
            "description": "Number of days for net payment terms.",
            "example": 30
          },
          "quoteUrl": {
            "type": "string",
            "description": "URL to view or manage the quote.",
            "example": "https://test.onfastspring.com/popup-defaultB2B/account/order/quote/AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient information for the quote.",
            "properties": {
              "company": {
                "type": "string",
                "nullable": true,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "description": "Recipient's phone number.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID for the recipient, if any.",
                "example": null
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "properties": {
              "addressLine1": {
                "type": "string",
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "nullable": true,
                "description": "Second line of the address, if needed.",
                "example": null
              },
              "city": {
                "type": "string",
                "description": "City of the address.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "description": "Country code or name.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "description": "Postal or ZIP code for the address.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "description": "State or region.",
                "example": "California"
              }
            }
          },
          "siteId": {
            "type": "string",
            "description": "Identifier of the site associated with this quote.",
            "example": "aB0CdEFGHIjK"
          },
          "status": {
            "type": "string",
            "description": "Current status of the quote.",
            "example": "EXPIRED",
            "enum": [
              "OPEN",
              "CANCELED",
              "AWAITING_PAYMENT",
              "COMPLETED",
              "EXPIRED"
            ]
          },
          "statusHistory": {
            "type": "array",
            "description": "Array of objects describing status transitions.",
            "items": {
              "type": "object",
              "properties": {
                "statusUpdatedTo": {
                  "type": "string",
                  "description": "The status to which the quote was updated.",
                  "example": "EXPIRED"
                },
                "statusUpdatedByFullName": {
                  "type": "string",
                  "description": "Full name of the person (or system) updating the status.",
                  "example": "SYSTEM SYSTEM"
                },
                "statusUpdatedByEmail": {
                  "type": "string",
                  "description": "Email of the user (or system) who updated the status.",
                  "example": "SYSTEM"
                },
                "statusUpdatedOn": {
                  "type": "string",
                  "format": "date-time",
                  "description": "Timestamp of the status update.",
                  "example": "2024-10-10T00:04:24.046+00:00"
                }
              }
            }
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal in base currency before discounts and tax.",
            "example": 400
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$400.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "description": "Subtotal in the payout currency.",
            "example": 400
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal in the payout currency.",
            "example": "$400.00"
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects associated with the quote.",
            "items": {
              "type": "object",
              "description": "A custom key-value pair for tagging.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "tax": {
            "type": "number",
            "description": "Total tax amount in base currency.",
            "example": 0
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied, if any.",
            "example": 0
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied (e.g., TAX, VAT).",
            "example": "TAX"
          },
          "total": {
            "type": "number",
            "description": "Final total in base currency, after discounts/tax.",
            "example": 360
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted display of the total amount.",
            "example": "$360.00"
          },
          "totalInPayoutCurrency": {
            "type": "number",
            "description": "Total in the payout currency.",
            "example": 360
          },
          "totalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the total in payout currency.",
            "example": "$360.00"
          },
          "updated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was last updated.",
            "example": "2024-10-10T00:04:24.174+00:00"
          },
          "taxId": {
            "type": "string",
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "nullable": true,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          },
          "orderReference": {
            "type": "string",
            "nullable": true,
            "description": "Reference to an associated order, if any.",
            "example": "AB1234567-8910-11121"
          },
          "isGrossTax": {
            "type": "boolean",
            "description": "Indicates whether the quote uses gross tax.",
            "example": false
          },
          "invoiceId": {
            "type": "string",
            "nullable": true,
            "description": "Invoice ID associated with this quote, if any.",
            "example": "AB12CDE35FGHIJKLMN6OPQRSTUV"
          }
        }
      }
    }
  }
}
```

Update a quote

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update a quote

Updates an existing quote with the given `quote_id` by setting the values of the request body passed. Any properties not provided will be left unchanged.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Quotes",
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
      "name": "Quotes",
      "description": "Create, list, retrieve, update, and cancel quotes.\n"
    }
  ],
  "paths": {
    "/quotes/{quote_id}": {
      "put": {
        "summary": "Update a quote",
        "tags": [
          "Quotes"
        ],
        "operationId": "updateQuote",
        "description": "Updates an existing quote with the given `quote_id` by setting the values of the request body passed. Any properties not provided will be left unchanged.",
        "parameters": [
          {
            "name": "quote_id",
            "in": "path",
            "required": true,
            "description": "A unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S",
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "updateQuoteRequest": {
                    "$ref": "#/components/schemas/UpdateQuoteRequest"
                  }
                }
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
                  "$ref": "#/components/schemas/QuoteResponse"
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
      "UpdateQuoteRequest": {
        "title": "Update a quote",
        "type": "object",
        "required": [
          "currency",
          "expirationDateDays",
          "fulfillmentTerm",
          "items",
          "name",
          "recipient",
          "recipientAddress"
        ],
        "properties": {
          "currency": {
            "type": "string",
            "minLength": 3,
            "maxLength": 3,
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "expirationDateDays": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "maximum": 90,
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ],
            "example": "ON_PAYMENT"
          },
          "items": {
            "type": "array",
            "minItems": 1,
            "maxItems": 2147483647,
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "description": "Item definition for the quote.",
              "required": [
                "product"
              ],
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "quantity": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in base currency.",
                  "example": 10
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "The name or label associated with the quote.",
            "example": "Name of the quote"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient (contact) information for the quote.",
            "required": [
              "email",
              "first",
              "last"
            ],
            "properties": {
              "company": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "nullable": true,
                "description": "Phone number of the contact, if any.",
                "example": "5555551234"
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "required": [
              "country",
              "postalCode"
            ],
            "properties": {
              "addressLine1": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "nullable": true,
                "description": "Second line of the address, if any.",
                "example": "Suite 101"
              },
              "city": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "City or municipality.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "minLength": 2,
                "maxLength": 2,
                "description": "2-letter country code.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "Postal or ZIP code.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "minLength": 0,
                "maxLength": 255,
                "description": "State, province, or region.",
                "example": "Example Region"
              }
            }
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "minLength": 0,
            "maxLength": 255,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "notes": {
            "type": "string",
            "minLength": 0,
            "maxLength": 5000,
            "description": "Additional notes for the quote.",
            "example": "This is a Note"
          },
          "netTermsDays": {
            "type": "integer",
            "format": "int32",
            "description": "Number of days for net payment terms."
          },
          "status": {
            "type": "string",
            "description": "Current status of the quote.",
            "enum": [
              "OPEN",
              "CANCELED",
              "AWAITING_PAYMENT",
              "COMPLETED",
              "EXPIRED"
            ],
            "example": "OPEN"
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects attached to the quote.",
            "items": {
              "type": "object",
              "description": "Key-value pair for tags.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "taxId": {
            "type": "string",
            "minLength": 0,
            "maxLength": 255,
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "minLength": 0,
            "maxLength": 255,
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "minLength": 0,
            "maxLength": 25,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          },
          "invoiceId": {
            "type": "string",
            "minLength": 0,
            "maxLength": 128,
            "description": "Identifier for the invoice associated with this quote, if any.",
            "example": "INV-12345"
          }
        }
      },
      "QuoteResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "buyerGenerated": {
            "type": "boolean",
            "description": "Indicates if the quote was created by the buyer.",
            "example": false
          },
          "copyNotesToInvoice": {
            "type": "boolean",
            "description": "Determines whether notes should be copied to the invoice.",
            "example": false
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was created.",
            "example": "2024-09-09T14:51:34.814+00:00"
          },
          "createdBy": {
            "type": "object",
            "description": "Information about the user who created this quote.",
            "properties": {
              "company": {
                "type": "string",
                "description": "Company name of the creator.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Email address of the creator.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "First name of the creator.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Last name of the creator.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "nullable": true,
                "description": "Phone number of the creator.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID associated with the creator, if available.",
                "example": "abCDEFGHijkLmNOpQr_S"
              }
            }
          },
          "currency": {
            "type": "string",
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount in base currency.",
            "example": 40
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in base currency.",
            "example": "$40.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "description": "Discount amount in the payout currency.",
            "example": 40
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in the payout currency.",
            "example": "$40.00"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote expires.",
            "example": "2024-10-09T14:51:34.814+00:00"
          },
          "expirationDateDays": {
            "type": "integer",
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "example": "ON_PAYMENT",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ]
          },
          "items": {
            "type": "array",
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "customPrice": {
                  "type": "boolean",
                  "description": "Indicates if the price is custom.",
                  "example": false
                },
                "display": {
                  "type": "string",
                  "description": "Display name for the product.",
                  "example": "Add-on Subscription"
                },
                "image": {
                  "type": "string",
                  "nullable": true,
                  "description": "Image filename or URL, if applicable.",
                  "example": null
                },
                "intervalCount": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of intervals (e.g., for a subscription).",
                  "example": null
                },
                "period": {
                  "type": "string",
                  "nullable": true,
                  "description": "Billing period for the product (e.g., Monthly).",
                  "example": "Monthly"
                },
                "periodDays": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of days in the billing period.",
                  "example": null
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "renewIntoProducts": {
                  "type": "array",
                  "uniqueItems": true,
                  "description": "Array of product(s) into which this item can renew.",
                  "items": {
                    "type": "object",
                    "description": "Details of the product that this item renews into.",
                    "properties": {
                      "renewIntoProductId": {
                        "type": "string",
                        "description": "Product ID into which the item renews.",
                        "example": "DG4UH337PVYDC33XEP3ZH3JHD"
                      },
                      "renewIntoPath": {
                        "type": "string",
                        "description": "Path or identifier for the renewing product.",
                        "example": "video-subscription"
                      },
                      "renewIntoPrice": {
                        "type": "number",
                        "format": "float",
                        "description": "Price for the renewing product.",
                        "example": 9.99
                      },
                      "renewIntoPeriod": {
                        "type": "string",
                        "description": "Period for the renewing product (e.g., Weekly, Monthly).",
                        "example": "Weekly"
                      },
                      "renewIntoLevel": {
                        "type": "integer",
                        "format": "int32",
                        "description": "Level or tier for the renewal.",
                        "example": 1
                      },
                      "renewIntoIntervalCount": {
                        "type": "string",
                        "description": "Interval count for the renewal (e.g., \"2\" months).",
                        "example": "2"
                      },
                      "renewIntoUpcomingProduct": {
                        "type": "string",
                        "description": "Upcoming product name or handle after renewal.",
                        "example": "audio-subscription"
                      }
                    }
                  }
                },
                "taxes": {
                  "type": "array",
                  "description": "Tax details for this item.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "taxValue": {
                        "type": "number",
                        "description": "Tax amount for this item.",
                        "example": 0
                      },
                      "totalTaxable": {
                        "type": "number",
                        "description": "Total taxable amount for this item.",
                        "example": 360
                      }
                    }
                  }
                },
                "trialDays": {
                  "type": "integer",
                  "nullable": true,
                  "description": "Number of trial days, if applicable.",
                  "example": null
                },
                "subscription": {
                  "type": "boolean",
                  "description": "Indicates if the item is a subscription.",
                  "example": true
                },
                "unitTrialPrice": {
                  "type": "number",
                  "description": "Trial price per unit, if applicable.",
                  "example": 0
                },
                "trialPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Trial price without tax, if any.",
                  "example": null
                },
                "trialExtendedPrice": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended price for the trial, if any.",
                  "example": null
                },
                "trialExtendedPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial price without tax.",
                  "example": null
                },
                "trialUnitTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Tax per unit during trial.",
                  "example": null
                },
                "trialExtendedTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial tax, if any.",
                  "example": null
                },
                "driver": {
                  "type": "string",
                  "nullable": true,
                  "description": "Driver or source reference, if any.",
                  "example": null
                },
                "type": {
                  "type": "string",
                  "nullable": true,
                  "description": "Additional type info.",
                  "example": null
                },
                "unitCouponDiscount": {
                  "type": "number",
                  "description": "Discount per unit from a coupon.",
                  "example": 0
                },
                "unitCouponDiscountDisplay": {
                  "type": "string",
                  "description": "Display format of coupon discount per unit.",
                  "example": "$0.00"
                },
                "unitCouponDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Coupon discount in the payout currency.",
                  "example": 0
                },
                "unitCouponDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the coupon discount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "description": "Amount of discount per unit in base currency.",
                  "example": 20
                },
                "unitDiscountWithoutTax": {
                  "type": "number",
                  "description": "Per-unit discount before tax.",
                  "example": 20
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$20.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit discount in payout currency.",
                  "example": 20
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the discount in payout currency.",
                  "example": "$20.00"
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in the base currency.",
                  "example": 200
                },
                "unitListPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price per unit in base currency.",
                  "example": "$200.00"
                },
                "unitListPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit list price in payout currency.",
                  "example": 200
                },
                "unitListPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price in payout currency.",
                  "example": "$200.00"
                },
                "unitPrice": {
                  "type": "number",
                  "description": "Final price per unit in base currency.",
                  "example": 180
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price.",
                  "example": "$180.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Final price per unit in the payout currency.",
                  "example": 180
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price in payout currency.",
                  "example": "$180.00"
                },
                "unitPriceWithoutTax": {
                  "type": "number",
                  "description": "Final unit price without tax.",
                  "example": 180
                },
                "unitPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the unit price without tax.",
                  "example": "$180.00"
                },
                "unitListPriceWithoutTax": {
                  "type": "number",
                  "description": "Original list price per unit before tax.",
                  "example": 200
                },
                "unitListPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price before tax.",
                  "example": "$200.00"
                },
                "grossModeOption": {
                  "type": "string",
                  "nullable": true,
                  "description": "Gross mode option, if applicable.",
                  "example": null
                },
                "taxMode": {
                  "type": "string",
                  "description": "Whether tax is NET or GROSS for this item.",
                  "example": "NET"
                },
                "priceDetail": {
                  "type": "object",
                  "description": "Detailed breakdown of the item’s pricing.",
                  "properties": {
                    "path": {
                      "type": "string",
                      "description": "Identifier or path for this item’s pricing context.",
                      "example": "add-on-subscription"
                    },
                    "quantity": {
                      "type": "integer",
                      "description": "Quantity used for this price detail.",
                      "example": 2
                    },
                    "plans": {
                      "type": "array",
                      "description": "Array of plan objects associated with pricing.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "path": {
                            "type": "string",
                            "description": "Plan’s product path.",
                            "example": "add-on-subscription"
                          },
                          "interval": {
                            "type": "string",
                            "description": "Interval name (e.g., month, year).",
                            "example": "month"
                          },
                          "intervalLength": {
                            "type": "integer",
                            "description": "Number of intervals per billing cycle.",
                            "example": 1
                          },
                          "prices": {
                            "type": "array",
                            "description": "Detailed price lines for each plan.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "path": {
                                  "type": "string",
                                  "description": "Product path for pricing context.",
                                  "example": "add-on-subscription"
                                },
                                "showUnitListPrice": {
                                  "type": "number",
                                  "description": "Displayed list price per unit.",
                                  "example": 200
                                },
                                "showUnitNetPrice": {
                                  "type": "number",
                                  "description": "Displayed net price per unit.",
                                  "example": 180
                                },
                                "showExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Displayed extended net price for all units.",
                                  "example": 360
                                },
                                "showExtendedListPrice": {
                                  "type": "number",
                                  "description": "Displayed extended list price.",
                                  "example": 400
                                },
                                "showUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed discount amount per unit.",
                                  "example": 20
                                },
                                "showExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed total discount across all units.",
                                  "example": 40
                                },
                                "showExtendedTaxes": {
                                  "type": "number",
                                  "description": "Displayed total extended taxes.",
                                  "example": 0
                                },
                                "showTaxPercent": {
                                  "type": "number",
                                  "description": "Displayed tax percentage.",
                                  "example": 0
                                },
                                "subscriptionUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price if this item is a subscription.",
                                  "example": 200
                                },
                                "subscriptionExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for the subscription.",
                                  "example": 360
                                },
                                "subscriptionUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per subscription unit.",
                                  "example": 20
                                },
                                "subscriptionTaxMode": {
                                  "type": "string",
                                  "description": "Tax mode for the subscription (NET or GROSS).",
                                  "example": "NET"
                                },
                                "unitListPrice": {
                                  "type": "number",
                                  "description": "Raw list price per unit in base currency.",
                                  "example": 200
                                },
                                "unitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit in base currency.",
                                  "example": 180
                                },
                                "unitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per unit in base currency.",
                                  "example": 20
                                },
                                "extendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price for all units.",
                                  "example": 400
                                },
                                "extendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for all units.",
                                  "example": 360
                                },
                                "extendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended total discount across all units.",
                                  "example": 40
                                },
                                "withTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price with tax included.",
                                  "example": 200
                                },
                                "withTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Per-unit net price with tax included.",
                                  "example": 180
                                },
                                "withTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price with tax included.",
                                  "example": 400
                                },
                                "withTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price with tax included.",
                                  "example": 360
                                },
                                "withTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount with tax included.",
                                  "example": 40
                                },
                                "withoutTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit without tax.",
                                  "example": 200
                                },
                                "withoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit without tax.",
                                  "example": 180
                                },
                                "withoutTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price without tax.",
                                  "example": 400
                                },
                                "withoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price without tax.",
                                  "example": 360
                                },
                                "withoutTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount without tax.",
                                  "example": 40
                                },
                                "discountPercent": {
                                  "type": "number",
                                  "description": "Percentage discount applied to this item.",
                                  "example": 10
                                },
                                "withTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount with tax.",
                                  "example": 40
                                },
                                "withoutTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount without tax.",
                                  "example": 40
                                },
                                "productDiscountPercent": {
                                  "type": "number",
                                  "description": "Discount percent specific to the product.",
                                  "example": 0
                                },
                                "roundedWithoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net unit price without tax.",
                                  "example": 180
                                },
                                "roundedWithoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded extended net price without tax.",
                                  "example": 360
                                },
                                "roundedExtendedTaxes": {
                                  "type": "number",
                                  "description": "Rounded extended taxes.",
                                  "example": 0
                                },
                                "roundedWithTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net price with tax included.",
                                  "example": 360
                                },
                                "unitTaxes": {
                                  "type": "number",
                                  "description": "Tax per unit.",
                                  "example": 0
                                },
                                "extendedTaxes": {
                                  "type": "number",
                                  "description": "Extended total taxes.",
                                  "example": 0
                                },
                                "taxPercent": {
                                  "type": "number",
                                  "description": "Tax percentage.",
                                  "example": 0
                                },
                                "sourceListPrice": {
                                  "type": "number",
                                  "description": "Source or original list price.",
                                  "example": 200
                                },
                                "sourceCurrency": {
                                  "type": "string",
                                  "description": "Currency code used by the source price.",
                                  "example": "USD"
                                },
                                "lineNumber": {
                                  "type": "string",
                                  "description": "Unique identifier for this price line.",
                                  "example": "LNNS3BGRMB65JEK1FF3N9W9FEEKC"
                                },
                                "withholdingAmount": {
                                  "type": "number",
                                  "description": "Withholding amount in base currency.",
                                  "example": 0
                                },
                                "withholdingAmountUSD": {
                                  "type": "number",
                                  "description": "Withholding amount in USD, if different.",
                                  "example": 0
                                },
                                "rules": {
                                  "type": "array",
                                  "description": "Array of pricing rules applied.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "name": {
                                        "type": "string",
                                        "description": "Name of the rule.",
                                        "example": "DetermineCurrencyCode"
                                      },
                                      "reason": {
                                        "type": "string",
                                        "description": "Reason for applying this rule.",
                                        "example": "Input currency"
                                      },
                                      "outputCurrency": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Currency determined by the rule.",
                                        "example": "USD"
                                      },
                                      "trial": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Indicates if a trial was factored.",
                                        "example": "false"
                                      },
                                      "endPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the final price after the rule.",
                                        "example": "$200.00 USD"
                                      },
                                      "priceModel": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Pricing model (e.g., \"PER_UNIT\").",
                                        "example": "PER_UNIT"
                                      },
                                      "startPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the price before the rule.",
                                        "example": null
                                      },
                                      "description": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Brief explanation of the rule.",
                                        "example": "Load price"
                                      },
                                      "operation": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Operation performed by the rule.",
                                        "example": "+ 0.0000"
                                      },
                                      "operation2": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Secondary operation detail."
                                      },
                                      "taxMode": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "NET or GROSS tax mode indicated by the rule.",
                                        "example": "NET"
                                      },
                                      "taxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Tax rate used by the rule.",
                                        "example": "0.0000"
                                      },
                                      "taxExempt": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Whether the item is tax exempt.",
                                        "example": "false"
                                      },
                                      "unitPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit price in the context of this rule.",
                                        "example": "180.0000"
                                      },
                                      "unitTotal": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit total used by the rule."
                                      },
                                      "effectiveTaxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Effective tax rate after the rule."
                                      },
                                      "quantity": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Quantity used by the rule.",
                                        "example": "2"
                                      },
                                      "withTaxNetUnitRoundAmount": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Rounding amount for net unit price with tax."
                                      }
                                    }
                                  }
                                },
                                "effectiveTaxMode": {
                                  "type": "string",
                                  "description": "Final tax mode determined by the plan.",
                                  "example": "NET"
                                },
                                "dateLimitsEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if date limits were enforced.",
                                  "example": false
                                },
                                "discountTiers": {
                                  "type": "array",
                                  "description": "Array of discount tier objects for this plan.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "min": {
                                        "type": "integer",
                                        "description": "Minimum quantity for this tier.",
                                        "example": 1
                                      },
                                      "withTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price with tax in this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price without tax for this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount without tax.",
                                        "example": 20
                                      },
                                      "withTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount with tax included.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage for this tier.",
                                        "example": 10
                                      },
                                      "type": {
                                        "type": "string",
                                        "description": "Discount type (e.g., VOLUME_PERCENT_OFF).",
                                        "example": "VOLUME_PERCENT_OFF"
                                      }
                                    }
                                  }
                                },
                                "discounts": {
                                  "type": "array",
                                  "description": "Discounts applied at the plan level.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "discountType": {
                                        "type": "string",
                                        "description": "Type of discount (e.g., \"VOLUME_PERCENT_OFF\").",
                                        "example": "VOLUME_PERCENT_OFF"
                                      },
                                      "discountDuration": {
                                        "type": "integer",
                                        "description": "How long (in intervals) this discount is valid.",
                                        "example": 5
                                      },
                                      "discountPath": {
                                        "type": "string",
                                        "description": "Path or identifier for this discount.",
                                        "example": "add-on-subscription"
                                      },
                                      "discountUnitAmount": {
                                        "type": "number",
                                        "description": "Discount amount per unit in base currency.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage applied.",
                                        "example": 10
                                      }
                                    }
                                  }
                                },
                                "withTaxStoreCurrencyExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price (store currency) with tax.",
                                  "example": 360
                                },
                                "withTaxUSDExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price in USD with tax.",
                                  "example": 360
                                },
                                "taxCode": {
                                  "type": "string",
                                  "description": "Code representing the tax classification.",
                                  "example": "DV021010"
                                },
                                "taxFormat": {
                                  "type": "string",
                                  "description": "Format or category of tax (e.g., \"DIGITAL_ONLY\").",
                                  "example": "DIGITAL_ONLY"
                                },
                                "pricingPlanRenew": {
                                  "type": "string",
                                  "description": "Renewal policy for the pricing plan (e.g., \"auto\").",
                                  "example": "auto"
                                },
                                "customPrice": {
                                  "type": "boolean",
                                  "description": "Indicates if a custom price was used.",
                                  "example": false
                                },
                                "paidTrial": {
                                  "type": "boolean",
                                  "description": "Indicates if the trial period is paid.",
                                  "example": false
                                },
                                "paymentRequired": {
                                  "type": "boolean",
                                  "description": "Whether payment is required for the plan.",
                                  "example": true
                                },
                                "reactivationEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if reactivation is allowed.",
                                  "example": false
                                },
                                "reactivationExpirationDays": {
                                  "type": "integer",
                                  "description": "Days before a reactivation link expires.",
                                  "example": 0
                                },
                                "taxExemptedUnitAmount": {
                                  "type": "number",
                                  "description": "Per-unit amount exempted from tax.",
                                  "example": 0
                                },
                                "taxExemptedExtendedAmount": {
                                  "type": "number",
                                  "description": "Extended total amount exempted from tax.",
                                  "example": 0
                                },
                                "withoutTaxExemptionUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit when not tax-exempt.",
                                  "example": 180
                                },
                                "withoutTaxExemptionUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit when not tax-exempt.",
                                  "example": 200
                                },
                                "quantity": {
                                  "type": "integer",
                                  "description": "Quantity used in pricing calculations.",
                                  "example": 2
                                },
                                "discountPath": {
                                  "type": "string",
                                  "description": "Path for applying the discount, if any.",
                                  "example": "add-on-subscription"
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                },
                "quantityBehavior": {
                  "type": "string",
                  "description": "Determines how quantity changes are handled.",
                  "example": "allow"
                },
                "quantityDefault": {
                  "type": "integer",
                  "description": "Default quantity if none is specified.",
                  "example": 1
                },
                "discountDuration": {
                  "type": "integer",
                  "description": "Number of intervals the discount remains valid.",
                  "example": 5
                },
                "cancellationChoice": {
                  "type": "string",
                  "description": "Cancellation policy (e.g., AFTER_LAST_NOTIFICATION).",
                  "example": "AFTER_LAST_NOTIFICATION"
                },
                "startNewTerm": {
                  "type": "boolean",
                  "description": "Whether a new subscription term should begin.",
                  "example": false
                },
                "subscriptionCancelDisabled": {
                  "type": "boolean",
                  "description": "Indicates if subscription cancellation is disabled.",
                  "example": false
                },
                "notifications": {
                  "type": "array",
                  "description": "Notification rules for different events.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "notificationType": {
                        "type": "string",
                        "description": "Type of notification (e.g., PAYMENT_REMINDER).",
                        "example": "TRIAL_CONVERSION_REMINDER"
                      },
                      "enabled": {
                        "type": "boolean",
                        "description": "Indicates if the notification is enabled.",
                        "example": false
                      },
                      "intervals": {
                        "type": "array",
                        "description": "Interval definitions for sending notifications.",
                        "items": {
                          "type": "object",
                          "properties": {
                            "intervalUnit": {
                              "type": "string",
                              "description": "Unit of interval (day, week, etc.).",
                              "example": "day"
                            },
                            "intervalLength": {
                              "type": "integer",
                              "description": "Length of each interval.",
                              "example": 3
                            },
                            "intervalCount": {
                              "type": "integer",
                              "nullable": true,
                              "description": "Number of times to repeat, if applicable."
                            }
                          }
                        }
                      },
                      "firstIntervalUnit": {
                        "type": "string",
                        "description": "Unit for the first notification interval.",
                        "example": "day"
                      },
                      "firstIntervalLength": {
                        "type": "integer",
                        "description": "Length of the first interval.",
                        "example": 3
                      },
                      "firstIntervalCount": {
                        "type": "integer",
                        "nullable": true,
                        "description": "How many times the first interval repeats."
                      }
                    }
                  }
                },
                "productDescriptions": {
                  "type": "object",
                  "description": "Localized display and summary of the product.",
                  "properties": {
                    "display": {
                      "type": "object",
                      "description": "Localized display text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "Add-on Subscription"
                        }
                      }
                    },
                    "summary": {
                      "type": "object",
                      "description": "Localized summary text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "This is my product summary"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "Name or label for the quote.",
            "example": "Name of the quote"
          },
          "notes": {
            "type": "string",
            "nullable": true,
            "description": "Additional notes for the quote.",
            "example": null
          },
          "netTermsDays": {
            "type": "integer",
            "description": "Number of days for net payment terms.",
            "example": 30
          },
          "quoteUrl": {
            "type": "string",
            "description": "URL to view or manage the quote.",
            "example": "https://test.onfastspring.com/popup-defaultB2B/account/order/quote/AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient information for the quote.",
            "properties": {
              "company": {
                "type": "string",
                "nullable": true,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "description": "Recipient's phone number.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID for the recipient, if any.",
                "example": null
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "properties": {
              "addressLine1": {
                "type": "string",
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "nullable": true,
                "description": "Second line of the address, if needed.",
                "example": null
              },
              "city": {
                "type": "string",
                "description": "City of the address.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "description": "Country code or name.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "description": "Postal or ZIP code for the address.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "description": "State or region.",
                "example": "California"
              }
            }
          },
          "siteId": {
            "type": "string",
            "description": "Identifier of the site associated with this quote.",
            "example": "aB0CdEFGHIjK"
          },
          "status": {
            "type": "string",
            "description": "Current status of the quote.",
            "example": "EXPIRED",
            "enum": [
              "OPEN",
              "CANCELED",
              "AWAITING_PAYMENT",
              "COMPLETED",
              "EXPIRED"
            ]
          },
          "statusHistory": {
            "type": "array",
            "description": "Array of objects describing status transitions.",
            "items": {
              "type": "object",
              "properties": {
                "statusUpdatedTo": {
                  "type": "string",
                  "description": "The status to which the quote was updated.",
                  "example": "EXPIRED"
                },
                "statusUpdatedByFullName": {
                  "type": "string",
                  "description": "Full name of the person (or system) updating the status.",
                  "example": "SYSTEM SYSTEM"
                },
                "statusUpdatedByEmail": {
                  "type": "string",
                  "description": "Email of the user (or system) who updated the status.",
                  "example": "SYSTEM"
                },
                "statusUpdatedOn": {
                  "type": "string",
                  "format": "date-time",
                  "description": "Timestamp of the status update.",
                  "example": "2024-10-10T00:04:24.046+00:00"
                }
              }
            }
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal in base currency before discounts and tax.",
            "example": 400
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$400.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "description": "Subtotal in the payout currency.",
            "example": 400
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal in the payout currency.",
            "example": "$400.00"
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects associated with the quote.",
            "items": {
              "type": "object",
              "description": "A custom key-value pair for tagging.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "tax": {
            "type": "number",
            "description": "Total tax amount in base currency.",
            "example": 0
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied, if any.",
            "example": 0
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied (e.g., TAX, VAT).",
            "example": "TAX"
          },
          "total": {
            "type": "number",
            "description": "Final total in base currency, after discounts/tax.",
            "example": 360
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted display of the total amount.",
            "example": "$360.00"
          },
          "totalInPayoutCurrency": {
            "type": "number",
            "description": "Total in the payout currency.",
            "example": 360
          },
          "totalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the total in payout currency.",
            "example": "$360.00"
          },
          "updated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was last updated.",
            "example": "2024-10-10T00:04:24.174+00:00"
          },
          "taxId": {
            "type": "string",
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "nullable": true,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          },
          "orderReference": {
            "type": "string",
            "nullable": true,
            "description": "Reference to an associated order, if any.",
            "example": "AB1234567-8910-11121"
          },
          "isGrossTax": {
            "type": "boolean",
            "description": "Indicates whether the quote uses gross tax.",
            "example": false
          },
          "invoiceId": {
            "type": "string",
            "nullable": true,
            "description": "Invoice ID associated with this quote, if any.",
            "example": "AB12CDE35FGHIJKLMN6OPQRSTUV"
          }
        }
      }
    }
  }
}
```

Cancel a quote

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Cancel a quote

Cancels a quote with the given `quote_id` and sets the status to `CANCELED`. Once canceled, the quote link is no longer available.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Quotes",
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
      "name": "Quotes",
      "description": "Create, list, retrieve, update, and cancel quotes.\n"
    }
  ],
  "paths": {
    "/quotes/{quote_id}/cancel": {
      "post": {
        "summary": "Cancel a quote",
        "tags": [
          "Quotes"
        ],
        "operationId": "cancelQuote",
        "description": "Cancels a quote with the given `quote_id` and sets the status to `CANCELED`. Once canceled, the quote link is no longer available.",
        "parameters": [
          {
            "name": "quote_id",
            "in": "path",
            "required": true,
            "description": "A unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S",
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
                  "$ref": "#/components/schemas/QuoteResponse"
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
      "QuoteResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the quote.",
            "example": "AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "buyerGenerated": {
            "type": "boolean",
            "description": "Indicates if the quote was created by the buyer.",
            "example": false
          },
          "copyNotesToInvoice": {
            "type": "boolean",
            "description": "Determines whether notes should be copied to the invoice.",
            "example": false
          },
          "coupon": {
            "type": "string",
            "nullable": true,
            "description": "Coupon code applied to the quote, if any.",
            "example": "TENOFF"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was created.",
            "example": "2024-09-09T14:51:34.814+00:00"
          },
          "createdBy": {
            "type": "object",
            "description": "Information about the user who created this quote.",
            "properties": {
              "company": {
                "type": "string",
                "description": "Company name of the creator.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Email address of the creator.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "First name of the creator.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Last name of the creator.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "nullable": true,
                "description": "Phone number of the creator.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID associated with the creator, if available.",
                "example": "abCDEFGHijkLmNOpQr_S"
              }
            }
          },
          "currency": {
            "type": "string",
            "description": "Currency code used in the quote.",
            "example": "USD"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount in base currency.",
            "example": 40
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in base currency.",
            "example": "$40.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "description": "Discount amount in the payout currency.",
            "example": 40
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the discount in the payout currency.",
            "example": "$40.00"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote expires.",
            "example": "2024-10-09T14:51:34.814+00:00"
          },
          "expirationDateDays": {
            "type": "integer",
            "description": "Number of days until the quote expires.",
            "example": 30
          },
          "fulfillmentTerm": {
            "type": "string",
            "description": "Defines when the quote is fulfilled (e.g., ON_PAYMENT).",
            "example": "ON_PAYMENT",
            "enum": [
              "ON_PAYMENT",
              "ON_QUOTE_ACCEPTANCE"
            ]
          },
          "items": {
            "type": "array",
            "description": "Array of items included in the quote.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product identifier or name.",
                  "example": "add-on-subscription"
                },
                "customPrice": {
                  "type": "boolean",
                  "description": "Indicates if the price is custom.",
                  "example": false
                },
                "display": {
                  "type": "string",
                  "description": "Display name for the product.",
                  "example": "Add-on Subscription"
                },
                "image": {
                  "type": "string",
                  "nullable": true,
                  "description": "Image filename or URL, if applicable.",
                  "example": null
                },
                "intervalCount": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of intervals (e.g., for a subscription).",
                  "example": null
                },
                "period": {
                  "type": "string",
                  "nullable": true,
                  "description": "Billing period for the product (e.g., Monthly).",
                  "example": "Monthly"
                },
                "periodDays": {
                  "type": "string",
                  "nullable": true,
                  "description": "Number of days in the billing period.",
                  "example": null
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product.",
                  "example": 2
                },
                "renewIntoProducts": {
                  "type": "array",
                  "uniqueItems": true,
                  "description": "Array of product(s) into which this item can renew.",
                  "items": {
                    "type": "object",
                    "description": "Details of the product that this item renews into.",
                    "properties": {
                      "renewIntoProductId": {
                        "type": "string",
                        "description": "Product ID into which the item renews.",
                        "example": "DG4UH337PVYDC33XEP3ZH3JHD"
                      },
                      "renewIntoPath": {
                        "type": "string",
                        "description": "Path or identifier for the renewing product.",
                        "example": "video-subscription"
                      },
                      "renewIntoPrice": {
                        "type": "number",
                        "format": "float",
                        "description": "Price for the renewing product.",
                        "example": 9.99
                      },
                      "renewIntoPeriod": {
                        "type": "string",
                        "description": "Period for the renewing product (e.g., Weekly, Monthly).",
                        "example": "Weekly"
                      },
                      "renewIntoLevel": {
                        "type": "integer",
                        "format": "int32",
                        "description": "Level or tier for the renewal.",
                        "example": 1
                      },
                      "renewIntoIntervalCount": {
                        "type": "string",
                        "description": "Interval count for the renewal (e.g., \"2\" months).",
                        "example": "2"
                      },
                      "renewIntoUpcomingProduct": {
                        "type": "string",
                        "description": "Upcoming product name or handle after renewal.",
                        "example": "audio-subscription"
                      }
                    }
                  }
                },
                "taxes": {
                  "type": "array",
                  "description": "Tax details for this item.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "taxValue": {
                        "type": "number",
                        "description": "Tax amount for this item.",
                        "example": 0
                      },
                      "totalTaxable": {
                        "type": "number",
                        "description": "Total taxable amount for this item.",
                        "example": 360
                      }
                    }
                  }
                },
                "trialDays": {
                  "type": "integer",
                  "nullable": true,
                  "description": "Number of trial days, if applicable.",
                  "example": null
                },
                "subscription": {
                  "type": "boolean",
                  "description": "Indicates if the item is a subscription.",
                  "example": true
                },
                "unitTrialPrice": {
                  "type": "number",
                  "description": "Trial price per unit, if applicable.",
                  "example": 0
                },
                "trialPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Trial price without tax, if any.",
                  "example": null
                },
                "trialExtendedPrice": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended price for the trial, if any.",
                  "example": null
                },
                "trialExtendedPriceWithoutTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial price without tax.",
                  "example": null
                },
                "trialUnitTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Tax per unit during trial.",
                  "example": null
                },
                "trialExtendedTax": {
                  "type": "number",
                  "nullable": true,
                  "description": "Extended trial tax, if any.",
                  "example": null
                },
                "driver": {
                  "type": "string",
                  "nullable": true,
                  "description": "Driver or source reference, if any.",
                  "example": null
                },
                "type": {
                  "type": "string",
                  "nullable": true,
                  "description": "Additional type info.",
                  "example": null
                },
                "unitCouponDiscount": {
                  "type": "number",
                  "description": "Discount per unit from a coupon.",
                  "example": 0
                },
                "unitCouponDiscountDisplay": {
                  "type": "string",
                  "description": "Display format of coupon discount per unit.",
                  "example": "$0.00"
                },
                "unitCouponDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Coupon discount in the payout currency.",
                  "example": 0
                },
                "unitCouponDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the coupon discount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "description": "Amount of discount per unit in base currency.",
                  "example": 20
                },
                "unitDiscountWithoutTax": {
                  "type": "number",
                  "description": "Per-unit discount before tax.",
                  "example": 20
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$20.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit discount in payout currency.",
                  "example": 20
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display format of the discount in payout currency.",
                  "example": "$20.00"
                },
                "unitListPrice": {
                  "type": "number",
                  "description": "List price per unit in the base currency.",
                  "example": 200
                },
                "unitListPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price per unit in base currency.",
                  "example": "$200.00"
                },
                "unitListPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Per-unit list price in payout currency.",
                  "example": 200
                },
                "unitListPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price in payout currency.",
                  "example": "$200.00"
                },
                "unitPrice": {
                  "type": "number",
                  "description": "Final price per unit in base currency.",
                  "example": 180
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price.",
                  "example": "$180.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "description": "Final price per unit in the payout currency.",
                  "example": 180
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the final unit price in payout currency.",
                  "example": "$180.00"
                },
                "unitPriceWithoutTax": {
                  "type": "number",
                  "description": "Final unit price without tax.",
                  "example": 180
                },
                "unitPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the unit price without tax.",
                  "example": "$180.00"
                },
                "unitListPriceWithoutTax": {
                  "type": "number",
                  "description": "Original list price per unit before tax.",
                  "example": 200
                },
                "unitListPriceWithoutTaxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the list price before tax.",
                  "example": "$200.00"
                },
                "grossModeOption": {
                  "type": "string",
                  "nullable": true,
                  "description": "Gross mode option, if applicable.",
                  "example": null
                },
                "taxMode": {
                  "type": "string",
                  "description": "Whether tax is NET or GROSS for this item.",
                  "example": "NET"
                },
                "priceDetail": {
                  "type": "object",
                  "description": "Detailed breakdown of the item’s pricing.",
                  "properties": {
                    "path": {
                      "type": "string",
                      "description": "Identifier or path for this item’s pricing context.",
                      "example": "add-on-subscription"
                    },
                    "quantity": {
                      "type": "integer",
                      "description": "Quantity used for this price detail.",
                      "example": 2
                    },
                    "plans": {
                      "type": "array",
                      "description": "Array of plan objects associated with pricing.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "path": {
                            "type": "string",
                            "description": "Plan’s product path.",
                            "example": "add-on-subscription"
                          },
                          "interval": {
                            "type": "string",
                            "description": "Interval name (e.g., month, year).",
                            "example": "month"
                          },
                          "intervalLength": {
                            "type": "integer",
                            "description": "Number of intervals per billing cycle.",
                            "example": 1
                          },
                          "prices": {
                            "type": "array",
                            "description": "Detailed price lines for each plan.",
                            "items": {
                              "type": "object",
                              "properties": {
                                "path": {
                                  "type": "string",
                                  "description": "Product path for pricing context.",
                                  "example": "add-on-subscription"
                                },
                                "showUnitListPrice": {
                                  "type": "number",
                                  "description": "Displayed list price per unit.",
                                  "example": 200
                                },
                                "showUnitNetPrice": {
                                  "type": "number",
                                  "description": "Displayed net price per unit.",
                                  "example": 180
                                },
                                "showExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Displayed extended net price for all units.",
                                  "example": 360
                                },
                                "showExtendedListPrice": {
                                  "type": "number",
                                  "description": "Displayed extended list price.",
                                  "example": 400
                                },
                                "showUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed discount amount per unit.",
                                  "example": 20
                                },
                                "showExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Displayed total discount across all units.",
                                  "example": 40
                                },
                                "showExtendedTaxes": {
                                  "type": "number",
                                  "description": "Displayed total extended taxes.",
                                  "example": 0
                                },
                                "showTaxPercent": {
                                  "type": "number",
                                  "description": "Displayed tax percentage.",
                                  "example": 0
                                },
                                "subscriptionUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price if this item is a subscription.",
                                  "example": 200
                                },
                                "subscriptionExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for the subscription.",
                                  "example": 360
                                },
                                "subscriptionUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per subscription unit.",
                                  "example": 20
                                },
                                "subscriptionTaxMode": {
                                  "type": "string",
                                  "description": "Tax mode for the subscription (NET or GROSS).",
                                  "example": "NET"
                                },
                                "unitListPrice": {
                                  "type": "number",
                                  "description": "Raw list price per unit in base currency.",
                                  "example": 200
                                },
                                "unitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit in base currency.",
                                  "example": 180
                                },
                                "unitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount amount per unit in base currency.",
                                  "example": 20
                                },
                                "extendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price for all units.",
                                  "example": 400
                                },
                                "extendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price for all units.",
                                  "example": 360
                                },
                                "extendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended total discount across all units.",
                                  "example": 40
                                },
                                "withTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "Per-unit list price with tax included.",
                                  "example": 200
                                },
                                "withTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Per-unit net price with tax included.",
                                  "example": 180
                                },
                                "withTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price with tax included.",
                                  "example": 400
                                },
                                "withTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price with tax included.",
                                  "example": 360
                                },
                                "withTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount with tax included.",
                                  "example": 40
                                },
                                "withoutTaxUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit without tax.",
                                  "example": 200
                                },
                                "withoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit without tax.",
                                  "example": 180
                                },
                                "withoutTaxUnitDiscountAmount": {
                                  "type": "number",
                                  "description": "Discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedListPrice": {
                                  "type": "number",
                                  "description": "Extended list price without tax.",
                                  "example": 400
                                },
                                "withoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price without tax.",
                                  "example": 360
                                },
                                "withoutTaxExtendedDiscountAmount": {
                                  "type": "number",
                                  "description": "Extended discount amount without tax.",
                                  "example": 40
                                },
                                "discountPercent": {
                                  "type": "number",
                                  "description": "Percentage discount applied to this item.",
                                  "example": 10
                                },
                                "withTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit, tax included.",
                                  "example": 20
                                },
                                "withTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount with tax.",
                                  "example": 40
                                },
                                "withoutTaxUnitProductDiscount": {
                                  "type": "number",
                                  "description": "Product discount per unit without tax.",
                                  "example": 20
                                },
                                "withoutTaxExtendedProductDiscount": {
                                  "type": "number",
                                  "description": "Extended product discount without tax.",
                                  "example": 40
                                },
                                "productDiscountPercent": {
                                  "type": "number",
                                  "description": "Discount percent specific to the product.",
                                  "example": 0
                                },
                                "roundedWithoutTaxUnitNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net unit price without tax.",
                                  "example": 180
                                },
                                "roundedWithoutTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded extended net price without tax.",
                                  "example": 360
                                },
                                "roundedExtendedTaxes": {
                                  "type": "number",
                                  "description": "Rounded extended taxes.",
                                  "example": 0
                                },
                                "roundedWithTaxExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Rounded net price with tax included.",
                                  "example": 360
                                },
                                "unitTaxes": {
                                  "type": "number",
                                  "description": "Tax per unit.",
                                  "example": 0
                                },
                                "extendedTaxes": {
                                  "type": "number",
                                  "description": "Extended total taxes.",
                                  "example": 0
                                },
                                "taxPercent": {
                                  "type": "number",
                                  "description": "Tax percentage.",
                                  "example": 0
                                },
                                "sourceListPrice": {
                                  "type": "number",
                                  "description": "Source or original list price.",
                                  "example": 200
                                },
                                "sourceCurrency": {
                                  "type": "string",
                                  "description": "Currency code used by the source price.",
                                  "example": "USD"
                                },
                                "lineNumber": {
                                  "type": "string",
                                  "description": "Unique identifier for this price line.",
                                  "example": "LNNS3BGRMB65JEK1FF3N9W9FEEKC"
                                },
                                "withholdingAmount": {
                                  "type": "number",
                                  "description": "Withholding amount in base currency.",
                                  "example": 0
                                },
                                "withholdingAmountUSD": {
                                  "type": "number",
                                  "description": "Withholding amount in USD, if different.",
                                  "example": 0
                                },
                                "rules": {
                                  "type": "array",
                                  "description": "Array of pricing rules applied.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "name": {
                                        "type": "string",
                                        "description": "Name of the rule.",
                                        "example": "DetermineCurrencyCode"
                                      },
                                      "reason": {
                                        "type": "string",
                                        "description": "Reason for applying this rule.",
                                        "example": "Input currency"
                                      },
                                      "outputCurrency": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Currency determined by the rule.",
                                        "example": "USD"
                                      },
                                      "trial": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Indicates if a trial was factored.",
                                        "example": "false"
                                      },
                                      "endPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the final price after the rule.",
                                        "example": "$200.00 USD"
                                      },
                                      "priceModel": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Pricing model (e.g., \"PER_UNIT\").",
                                        "example": "PER_UNIT"
                                      },
                                      "startPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Display of the price before the rule.",
                                        "example": null
                                      },
                                      "description": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Brief explanation of the rule.",
                                        "example": "Load price"
                                      },
                                      "operation": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Operation performed by the rule.",
                                        "example": "+ 0.0000"
                                      },
                                      "operation2": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Secondary operation detail."
                                      },
                                      "taxMode": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "NET or GROSS tax mode indicated by the rule.",
                                        "example": "NET"
                                      },
                                      "taxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Tax rate used by the rule.",
                                        "example": "0.0000"
                                      },
                                      "taxExempt": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Whether the item is tax exempt.",
                                        "example": "false"
                                      },
                                      "unitPrice": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit price in the context of this rule.",
                                        "example": "180.0000"
                                      },
                                      "unitTotal": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Unit total used by the rule."
                                      },
                                      "effectiveTaxRate": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Effective tax rate after the rule."
                                      },
                                      "quantity": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Quantity used by the rule.",
                                        "example": "2"
                                      },
                                      "withTaxNetUnitRoundAmount": {
                                        "type": "string",
                                        "nullable": true,
                                        "description": "Rounding amount for net unit price with tax."
                                      }
                                    }
                                  }
                                },
                                "effectiveTaxMode": {
                                  "type": "string",
                                  "description": "Final tax mode determined by the plan.",
                                  "example": "NET"
                                },
                                "dateLimitsEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if date limits were enforced.",
                                  "example": false
                                },
                                "discountTiers": {
                                  "type": "array",
                                  "description": "Array of discount tier objects for this plan.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "min": {
                                        "type": "integer",
                                        "description": "Minimum quantity for this tier.",
                                        "example": 1
                                      },
                                      "withTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price with tax in this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitNetPrice": {
                                        "type": "number",
                                        "description": "Unit net price without tax for this tier.",
                                        "example": 180
                                      },
                                      "withoutTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount without tax.",
                                        "example": 20
                                      },
                                      "withTaxUnitDiscountAmount": {
                                        "type": "number",
                                        "description": "Per-unit discount amount with tax included.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage for this tier.",
                                        "example": 10
                                      },
                                      "type": {
                                        "type": "string",
                                        "description": "Discount type (e.g., VOLUME_PERCENT_OFF).",
                                        "example": "VOLUME_PERCENT_OFF"
                                      }
                                    }
                                  }
                                },
                                "discounts": {
                                  "type": "array",
                                  "description": "Discounts applied at the plan level.",
                                  "items": {
                                    "type": "object",
                                    "properties": {
                                      "discountType": {
                                        "type": "string",
                                        "description": "Type of discount (e.g., \"VOLUME_PERCENT_OFF\").",
                                        "example": "VOLUME_PERCENT_OFF"
                                      },
                                      "discountDuration": {
                                        "type": "integer",
                                        "description": "How long (in intervals) this discount is valid.",
                                        "example": 5
                                      },
                                      "discountPath": {
                                        "type": "string",
                                        "description": "Path or identifier for this discount.",
                                        "example": "add-on-subscription"
                                      },
                                      "discountUnitAmount": {
                                        "type": "number",
                                        "description": "Discount amount per unit in base currency.",
                                        "example": 20
                                      },
                                      "discountPercent": {
                                        "type": "number",
                                        "description": "Discount percentage applied.",
                                        "example": 10
                                      }
                                    }
                                  }
                                },
                                "withTaxStoreCurrencyExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price (store currency) with tax.",
                                  "example": 360
                                },
                                "withTaxUSDExtendedNetPrice": {
                                  "type": "number",
                                  "description": "Extended net price in USD with tax.",
                                  "example": 360
                                },
                                "taxCode": {
                                  "type": "string",
                                  "description": "Code representing the tax classification.",
                                  "example": "DV021010"
                                },
                                "taxFormat": {
                                  "type": "string",
                                  "description": "Format or category of tax (e.g., \"DIGITAL_ONLY\").",
                                  "example": "DIGITAL_ONLY"
                                },
                                "pricingPlanRenew": {
                                  "type": "string",
                                  "description": "Renewal policy for the pricing plan (e.g., \"auto\").",
                                  "example": "auto"
                                },
                                "customPrice": {
                                  "type": "boolean",
                                  "description": "Indicates if a custom price was used.",
                                  "example": false
                                },
                                "paidTrial": {
                                  "type": "boolean",
                                  "description": "Indicates if the trial period is paid.",
                                  "example": false
                                },
                                "paymentRequired": {
                                  "type": "boolean",
                                  "description": "Whether payment is required for the plan.",
                                  "example": true
                                },
                                "reactivationEnabled": {
                                  "type": "boolean",
                                  "description": "Indicates if reactivation is allowed.",
                                  "example": false
                                },
                                "reactivationExpirationDays": {
                                  "type": "integer",
                                  "description": "Days before a reactivation link expires.",
                                  "example": 0
                                },
                                "taxExemptedUnitAmount": {
                                  "type": "number",
                                  "description": "Per-unit amount exempted from tax.",
                                  "example": 0
                                },
                                "taxExemptedExtendedAmount": {
                                  "type": "number",
                                  "description": "Extended total amount exempted from tax.",
                                  "example": 0
                                },
                                "withoutTaxExemptionUnitNetPrice": {
                                  "type": "number",
                                  "description": "Net price per unit when not tax-exempt.",
                                  "example": 180
                                },
                                "withoutTaxExemptionUnitListPrice": {
                                  "type": "number",
                                  "description": "List price per unit when not tax-exempt.",
                                  "example": 200
                                },
                                "quantity": {
                                  "type": "integer",
                                  "description": "Quantity used in pricing calculations.",
                                  "example": 2
                                },
                                "discountPath": {
                                  "type": "string",
                                  "description": "Path for applying the discount, if any.",
                                  "example": "add-on-subscription"
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                },
                "quantityBehavior": {
                  "type": "string",
                  "description": "Determines how quantity changes are handled.",
                  "example": "allow"
                },
                "quantityDefault": {
                  "type": "integer",
                  "description": "Default quantity if none is specified.",
                  "example": 1
                },
                "discountDuration": {
                  "type": "integer",
                  "description": "Number of intervals the discount remains valid.",
                  "example": 5
                },
                "cancellationChoice": {
                  "type": "string",
                  "description": "Cancellation policy (e.g., AFTER_LAST_NOTIFICATION).",
                  "example": "AFTER_LAST_NOTIFICATION"
                },
                "startNewTerm": {
                  "type": "boolean",
                  "description": "Whether a new subscription term should begin.",
                  "example": false
                },
                "subscriptionCancelDisabled": {
                  "type": "boolean",
                  "description": "Indicates if subscription cancellation is disabled.",
                  "example": false
                },
                "notifications": {
                  "type": "array",
                  "description": "Notification rules for different events.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "notificationType": {
                        "type": "string",
                        "description": "Type of notification (e.g., PAYMENT_REMINDER).",
                        "example": "TRIAL_CONVERSION_REMINDER"
                      },
                      "enabled": {
                        "type": "boolean",
                        "description": "Indicates if the notification is enabled.",
                        "example": false
                      },
                      "intervals": {
                        "type": "array",
                        "description": "Interval definitions for sending notifications.",
                        "items": {
                          "type": "object",
                          "properties": {
                            "intervalUnit": {
                              "type": "string",
                              "description": "Unit of interval (day, week, etc.).",
                              "example": "day"
                            },
                            "intervalLength": {
                              "type": "integer",
                              "description": "Length of each interval.",
                              "example": 3
                            },
                            "intervalCount": {
                              "type": "integer",
                              "nullable": true,
                              "description": "Number of times to repeat, if applicable."
                            }
                          }
                        }
                      },
                      "firstIntervalUnit": {
                        "type": "string",
                        "description": "Unit for the first notification interval.",
                        "example": "day"
                      },
                      "firstIntervalLength": {
                        "type": "integer",
                        "description": "Length of the first interval.",
                        "example": 3
                      },
                      "firstIntervalCount": {
                        "type": "integer",
                        "nullable": true,
                        "description": "How many times the first interval repeats."
                      }
                    }
                  }
                },
                "productDescriptions": {
                  "type": "object",
                  "description": "Localized display and summary of the product.",
                  "properties": {
                    "display": {
                      "type": "object",
                      "description": "Localized display text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "Add-on Subscription"
                        }
                      }
                    },
                    "summary": {
                      "type": "object",
                      "description": "Localized summary text.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "example": "This is my product summary"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "name": {
            "type": "string",
            "description": "Name or label for the quote.",
            "example": "Name of the quote"
          },
          "notes": {
            "type": "string",
            "nullable": true,
            "description": "Additional notes for the quote.",
            "example": null
          },
          "netTermsDays": {
            "type": "integer",
            "description": "Number of days for net payment terms.",
            "example": 30
          },
          "quoteUrl": {
            "type": "string",
            "description": "URL to view or manage the quote.",
            "example": "https://test.onfastspring.com/popup-defaultB2B/account/order/quote/AB1CD23EFG45H6IJKLMNOPQ78R9S"
          },
          "recipient": {
            "type": "object",
            "description": "Recipient information for the quote.",
            "properties": {
              "company": {
                "type": "string",
                "nullable": true,
                "description": "Recipient's company name, if any.",
                "example": "Example Company"
              },
              "email": {
                "type": "string",
                "description": "Recipient's email address.",
                "example": "jane.doe@example.com"
              },
              "first": {
                "type": "string",
                "description": "Recipient's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Recipient's last name.",
                "example": "Doe"
              },
              "phone": {
                "type": "string",
                "description": "Recipient's phone number.",
                "example": "5555551234"
              },
              "userId": {
                "type": "string",
                "nullable": true,
                "description": "Unique user ID for the recipient, if any.",
                "example": null
              }
            }
          },
          "recipientAddress": {
            "type": "object",
            "description": "Address of the quote recipient.",
            "properties": {
              "addressLine1": {
                "type": "string",
                "description": "First line of the address.",
                "example": "123 Main Street"
              },
              "addressLine2": {
                "type": "string",
                "nullable": true,
                "description": "Second line of the address, if needed.",
                "example": null
              },
              "city": {
                "type": "string",
                "description": "City of the address.",
                "example": "Example City"
              },
              "country": {
                "type": "string",
                "description": "Country code or name.",
                "example": "US"
              },
              "postalCode": {
                "type": "string",
                "description": "Postal or ZIP code for the address.",
                "example": "12345"
              },
              "region": {
                "type": "string",
                "description": "State or region.",
                "example": "California"
              }
            }
          },
          "siteId": {
            "type": "string",
            "description": "Identifier of the site associated with this quote.",
            "example": "aB0CdEFGHIjK"
          },
          "status": {
            "type": "string",
            "description": "Current status of the quote.",
            "example": "EXPIRED",
            "enum": [
              "OPEN",
              "CANCELED",
              "AWAITING_PAYMENT",
              "COMPLETED",
              "EXPIRED"
            ]
          },
          "statusHistory": {
            "type": "array",
            "description": "Array of objects describing status transitions.",
            "items": {
              "type": "object",
              "properties": {
                "statusUpdatedTo": {
                  "type": "string",
                  "description": "The status to which the quote was updated.",
                  "example": "EXPIRED"
                },
                "statusUpdatedByFullName": {
                  "type": "string",
                  "description": "Full name of the person (or system) updating the status.",
                  "example": "SYSTEM SYSTEM"
                },
                "statusUpdatedByEmail": {
                  "type": "string",
                  "description": "Email of the user (or system) who updated the status.",
                  "example": "SYSTEM"
                },
                "statusUpdatedOn": {
                  "type": "string",
                  "format": "date-time",
                  "description": "Timestamp of the status update.",
                  "example": "2024-10-10T00:04:24.046+00:00"
                }
              }
            }
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal in base currency before discounts and tax.",
            "example": 400
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$400.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "description": "Subtotal in the payout currency.",
            "example": 400
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal in the payout currency.",
            "example": "$400.00"
          },
          "tags": {
            "type": "array",
            "description": "Array of tag objects associated with the quote.",
            "items": {
              "type": "object",
              "description": "A custom key-value pair for tagging.",
              "properties": {
                "key": {
                  "type": "string",
                  "description": "The tag key or name.",
                  "example": "tag-key"
                },
                "value": {
                  "type": "string",
                  "description": "The tag value associated with this key.",
                  "example": "Tag Value"
                }
              }
            }
          },
          "tax": {
            "type": "number",
            "description": "Total tax amount in base currency.",
            "example": 0
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied, if any.",
            "example": 0
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied (e.g., TAX, VAT).",
            "example": "TAX"
          },
          "total": {
            "type": "number",
            "description": "Final total in base currency, after discounts/tax.",
            "example": 360
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted display of the total amount.",
            "example": "$360.00"
          },
          "totalInPayoutCurrency": {
            "type": "number",
            "description": "Total in the payout currency.",
            "example": 360
          },
          "totalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted display of the total in payout currency.",
            "example": "$360.00"
          },
          "updated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the quote was last updated.",
            "example": "2024-10-10T00:04:24.174+00:00"
          },
          "taxId": {
            "type": "string",
            "nullable": true,
            "description": "Tax ID associated with the quote, if any.",
            "example": "BE09999999XX"
          },
          "source": {
            "type": "string",
            "description": "Source from which the quote was generated (e.g., MANAGER).",
            "example": "MANAGER"
          },
          "sourceIP": {
            "type": "string",
            "nullable": true,
            "description": "IP address from which the quote was created or updated.",
            "example": "198.51.100.45"
          },
          "orderReference": {
            "type": "string",
            "nullable": true,
            "description": "Reference to an associated order, if any.",
            "example": "AB1234567-8910-11121"
          },
          "isGrossTax": {
            "type": "boolean",
            "description": "Indicates whether the quote uses gross tax.",
            "example": false
          },
          "invoiceId": {
            "type": "string",
            "nullable": true,
            "description": "Invoice ID associated with this quote, if any.",
            "example": "AB12CDE35FGHIJKLMN6OPQRSTUV"
          }
        }
      }
    }
  }
}
```