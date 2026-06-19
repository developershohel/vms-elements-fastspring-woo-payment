Create an invoice

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create an invoice

Creates and finalizes a new invoice with custom contacts, items, pricing, and optional payment configuration.

This endpoint returns a detailed invoice object, including totals, payment URLs, and contact metadata, based on the request body provided.      


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "The Invoices API handles invoice generation, retrieval, and management.\nGenerate invoices for orders and subscriptions, and manage billing documents.\n\n## Authentication\nAll endpoints require HTTP Basic Auth using your API credentials.\n\n\n**Rate Limiting:** All API endpoints are rate limited to 250 requests per IP address per minute. When exceeded, the API returns HTTP 429 with a `Retry-After` header.\n",
    "version": "v1.1.0-kgi27",
    "title": "FastSpring API - Invoices",
    "contact": {
      "name": "FastSpring Developer Support",
      "url": "https://developer.fastspring.com/support",
      "email": "devsupport@fastspring.com"
    }
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
      "name": "Invoices",
      "description": "Create, retrieve, and manage payment invoices with support for contact, address, organization, item, and metadata structures.\n"
    }
  ],
  "paths": {
    "/invoices/paymentInvoice": {
      "post": {
        "summary": "Create an invoice",
        "tags": [
          "Invoices"
        ],
        "description": "Creates and finalizes a new invoice with custom contacts, items, pricing, and optional payment configuration.\n\nThis endpoint returns a detailed invoice object, including totals, payment URLs, and contact metadata, based on the request body provided.      \n",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/InvoiceRequest"
              },
              "example": {
                "currencyCode": "USD",
                "languageCode": "en",
                "autoConvertPaymentCurrency": true,
                "contacts": [
                  {
                    "contactType": "deliverTo",
                    "contact": {
                      "email": "recipient@example.com",
                      "firstName": "John",
                      "lastName": "Smith",
                      "phoneNumber": "+1 555 000-0001",
                      "companyName": "Example Company",
                      "website": "https://example.com"
                    },
                    "address": {
                      "addressLine1": "123 Main Street",
                      "addressLine2": "Suite 100",
                      "city": "Metropolis",
                      "postalCode": "12345",
                      "country": "US"
                    },
                    "organization": {
                      "organizationName": "Example Company",
                      "organizationType": "S Corporation"
                    }
                  },
                  {
                    "contactType": "billTo",
                    "contact": {
                      "email": "billing@example.com",
                      "firstName": "Jane",
                      "lastName": "Doe",
                      "phoneNumber": "+1 555 000-0002",
                      "companyName": "Sample Corp",
                      "website": "https://samplecorp.example",
                      "fullName": "Jane Doe"
                    },
                    "address": {
                      "addressLine1": "456 Elm Street",
                      "addressLine2": "Building B",
                      "city": "Gotham",
                      "region": "CA",
                      "postalCode": "90210",
                      "country": "US"
                    },
                    "organization": {
                      "organizationName": "Sample Corp",
                      "organizationType": "Corporation",
                      "organizationReference": "REF-001"
                    }
                  }
                ],
                "dueDate": "2026-12-01T00:00:00Z",
                "invoiceNote": "Sample invoice note for reference.",
                "invoiceItems": [
                  {
                    "productPath": "sample-product",
                    "quantity": 4,
                    "sku": "SKU-123",
                    "useCatalogPricing": true,
                    "display": "Sample Product Display Name",
                    "summary": "Short product summary here.",
                    "extendedItemDescription": "Extended product description with additional details."
                  }
                ],
                "paymentMethod": "CARD",
                "mode": "TEST",
                "tagsJson": "{\"costCenter\": \"CC-1042\", \"purchaseOrder\": \"PO-2026-Q2-0042\"}"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/InvoicePostResponse"
                },
                "example": {
                  "id": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ",
                  "acquisitionTransactionId": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ",
                  "companyName": "recipient@example.com",
                  "createdOn": "2026-04-02T16:08:24Z",
                  "currency": "USD",
                  "dueDate": "2026-12-31T00:00:00Z",
                  "items": [
                    {
                      "id": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U",
                      "acquisitionTransactionItemId": "aB0Cd1E2FGh3Ij-K4LMNop",
                      "display": "Sample Product Display Name",
                      "invoiceId": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ",
                      "parentId": null,
                      "childItems": [],
                      "productFormat": "DIGITAL",
                      "productPath": "sample-product",
                      "productType": "BASE",
                      "listPrice": 25,
                      "freeTrialPeriod": null,
                      "period": null,
                      "periodCount": null,
                      "periodEndDate": null,
                      "periodPrice": null,
                      "shippingPrice": null,
                      "totalPreShippingPrice": 100,
                      "itemTaxes": [
                        {
                          "id": "ABCDEF0G1HI2JK3LMN4OPQRSTU5V",
                          "itemId": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U",
                          "value": 6,
                          "taxRate": 6,
                          "fromGross": null,
                          "description": null,
                          "taxType": "US_SALES_TAX"
                        }
                      ],
                      "subscription": false,
                      "autoRenew": null,
                      "taxExempt": false,
                      "quantity": 4,
                      "siblingPosition": null,
                      "extendedItemDescription": "Extended product description with additional details."
                    }
                  ],
                  "status": "OPEN",
                  "language": "en",
                  "invoiceSecret": "TAX01ABCDEFGHIJ2KLMNOP3QRSTUV4WXYZABC5DEFGHIJKLMNOPQ",
                  "invoiceType": "INVOICE",
                  "orderReference": "ABC012345-6789-01234",
                  "paymentReceiverAddress": {
                    "id": "ABCDE01FGHIJK2LMNOP3QRST4UVW",
                    "city": "Springfield",
                    "country": "US",
                    "postalCode": "54321",
                    "region": "IL",
                    "regionCustom": null,
                    "addressLine1": "789 Payment Lane",
                    "addressLine2": null,
                    "addressServiceId": null
                  },
                  "paymentReceiver": {
                    "id": "AB0CD1EFGHIJKLMNOPQRSTUVW23X",
                    "email": "invoicing@example.com",
                    "firstName": "Invoice",
                    "lastName": "Bot",
                    "companyName": null,
                    "phoneNumber": null,
                    "fullName": "Invoice Bot",
                    "website": null,
                    "contactType": "PAYMENT_FACILITATOR",
                    "contactServiceId": null,
                    "contactOrganizationId": null
                  },
                  "purchaserAddress": {
                    "id": "ABCDEFGHI01JKLMNOPQRSTUVW2XY",
                    "city": "Gotham",
                    "country": "US",
                    "postalCode": "90210",
                    "region": "CA",
                    "regionCustom": null,
                    "addressLine1": "456 Elm Street",
                    "addressLine2": "Building B",
                    "addressServiceId": null
                  },
                  "purchaser": {
                    "id": "ABCDEF0GHIJKLMNO1PQRST2U3VWX",
                    "email": "billing@example.com",
                    "firstName": "Jane",
                    "lastName": "Doe",
                    "companyName": "Sample Corp",
                    "phoneNumber": "+1 555 000-0002",
                    "fullName": "Jane Doe",
                    "website": "https://samplecorp.example",
                    "contactType": null,
                    "contactServiceId": null,
                    "contactOrganizationId": null
                  },
                  "receiverAddress": {
                    "id": "ABC0DEFGHIJKL1MNOP23Q4RSTUVW",
                    "addressLine1": "123 Main Street",
                    "addressLine2": "Suite 100",
                    "city": "Metropolis",
                    "region": "CA",
                    "postalCode": "12345",
                    "country": "US",
                    "regionCustom": null,
                    "addressServiceId": null
                  },
                  "receiver": {
                    "id": "ABC0DEFGHI1JKL2MN3OPQRS4TUVW",
                    "email": "recipient@example.com",
                    "firstName": "John",
                    "lastName": "Smith",
                    "fullName": "John Smith",
                    "companyName": "Example Company",
                    "phoneNumber": "+1 555 000-0001",
                    "website": "https://example.com",
                    "contactType": null,
                    "contactServiceId": null,
                    "contactOrganizationId": null
                  },
                  "shippingTotal": 0,
                  "subTotal": 100,
                  "siteId": "ABC0DE1GHIJ2",
                  "taxRate": 6,
                  "taxType": "US_SALES_TAX",
                  "totalOrderValue": 106,
                  "totalDiscountValue": 0,
                  "totalTaxValue": 6,
                  "version": "2",
                  "notes": "Sample invoice note for reference.",
                  "receiverOrganization": {
                    "organizationId": "ORGABCDEFGHIJKLMNOPQRST0U1VWX",
                    "organizationName": "Example Company",
                    "organizationType": "S Corporation"
                  },
                  "purchaserOrganization": {
                    "organizationId": "ORG0ABCDEF1GHIJ2KLMN3OPQRST45",
                    "organizationName": "Sample Corp",
                    "organizationType": "Corporation",
                    "organizationReference": "REF-001"
                  },
                  "paymentInvoiceWebLink": "https://example.com/account/order/ABC012345-6789-01234/invoice/ABCD0EF1GHIJKLMNOPQRSTUVWXYZ",
                  "paymentInvoicePdfLink": "https://example.com/account/order/ABC012345-6789-01234/invoice/ABCD0EF1GHIJKLMNOPQRSTUVWXYZ/pdf",
                  "paymentInvoiceWebPayLink": "https://example.com/session/ABC012345-6789-01234/pay",
                  "paymentCurrencyCode": "USD",
                  "paymentOrderReference": "ABC012345-6789-01234",
                  "paymentDueDate": "2026-12-01T00:00:00Z",
                  "paymentTotals": {
                    "payableTotalDisplay": "$106.00",
                    "subTotalDisplay": "$100.00",
                    "discountTotalDisplay": "$0.00",
                    "taxTotalDisplay": "$6.00",
                    "shippingTotalDisplay": "$0.00",
                    "taxExemptionReason": "US_SALES_TAX",
                    "payableTotal": 106,
                    "subTotal": 100,
                    "discountTotal": 0,
                    "taxTotal": 6,
                    "feesTotal": 0,
                    "shippingTotal": 0,
                    "incomeTotal": 0,
                    "payoutFeesTotal": 0,
                    "payoutTaxTotal": 0,
                    "payoutTotal": 0
                  },
                  "tagsJson": "{\"costCenter\": \"CC-1042\", \"purchaseOrder\": \"PO-2026-Q2-0042\"}"
                }
              }
            }
          },
          "401": {
            "description": "Authentication credentials are missing or invalid.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
                }
              }
            }
          },
          "429": {
            "description": "Rate limit exceeded. Retry after the number of seconds specified in the Retry-After header.",
            "headers": {
              "Retry-After": {
                "description": "Number of seconds to wait before retrying",
                "schema": {
                  "type": "integer"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
                }
              }
            }
          },
          "500": {
            "description": "Internal server error.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
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
      "InvoicePostResponse": {
        "type": "object",
        "description": "Full response returned when retrieving or creating an invoice.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the invoice.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "acquisitionTransactionId": {
            "type": "string",
            "description": "ID of the transaction that created the invoice.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "companyName": {
            "type": "string",
            "description": "Name of the company or user issuing the invoice.",
            "example": "recipient@example.com"
          },
          "createdOn": {
            "type": "string",
            "format": "date-time",
            "description": "Date and time when the invoice was created.",
            "example": "2026-04-02T16:08:24Z"
          },
          "currency": {
            "type": "string",
            "description": "ISO 4217 currency code.",
            "example": "USD"
          },
          "dueDate": {
            "type": "string",
            "format": "date-time",
            "description": "Invoice due date.",
            "example": "2026-12-31T00:00:00Z"
          },
          "items": {
            "type": "array",
            "description": "List of invoice line items.",
            "items": {
              "$ref": "#/components/schemas/InvoiceItemResponse"
            }
          },
          "status": {
            "$ref": "#/components/schemas/InvoiceStatus"
          },
          "language": {
            "type": "string",
            "description": "Language used for invoice content.",
            "example": "en"
          },
          "invoiceSecret": {
            "type": "string",
            "description": "Secure token to view the invoice.",
            "example": "TAX01ABCDEFGHIJ2KLMNOP3QRSTUV4WXYZABC5DEFGHIJKLMNOPQ"
          },
          "invoiceType": {
            "$ref": "#/components/schemas/InvoiceType"
          },
          "orderReference": {
            "type": "string",
            "description": "External reference for the order.",
            "example": "ABC012345-6789-01234"
          },
          "paymentReceiver": {
            "$ref": "#/components/schemas/Contact"
          },
          "paymentReceiverAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "purchaser": {
            "$ref": "#/components/schemas/Contact"
          },
          "purchaserAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "purchaserOrganization": {
            "$ref": "#/components/schemas/InvoiceOrganization"
          },
          "receiver": {
            "$ref": "#/components/schemas/Contact"
          },
          "receiverAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "receiverOrganization": {
            "$ref": "#/components/schemas/InvoiceOrganization"
          },
          "shippingTotal": {
            "type": "number",
            "description": "Total shipping cost.",
            "example": 0
          },
          "subTotal": {
            "type": "number",
            "description": "Subtotal amount before tax and discounts.",
            "example": 100
          },
          "siteId": {
            "type": "string",
            "description": "FastSpring site ID.",
            "example": "ABC0DE1GHIJ2"
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied to the invoice.",
            "example": 6
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied.",
            "example": "US_SALES_TAX"
          },
          "totalOrderValue": {
            "type": "number",
            "description": "Final order value after taxes and discounts.",
            "example": 106
          },
          "totalDiscountValue": {
            "type": "number",
            "description": "Total discounts applied to the invoice.",
            "example": 0
          },
          "totalTaxValue": {
            "type": "number",
            "description": "Total tax applied to the invoice.",
            "example": 6
          },
          "version": {
            "type": "string",
            "description": "Invoice version number.",
            "example": "2"
          },
          "notes": {
            "type": "string",
            "description": "Invoice notes for internal or customer reference.",
            "example": "Sample invoice note for reference."
          },
          "paymentInvoiceWebLink": {
            "type": "string",
            "description": "Web URL to view the invoice.",
            "example": "https://example.com/account/order/ABC012345-6789-01234/invoice/ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "paymentInvoicePdfLink": {
            "type": "string",
            "description": "Direct download URL for invoice PDF.",
            "example": "https://example.com/account/order/ABC012345-6789-01234/invoice/ABCD0EF1GHIJKLMNOPQRSTUVWXYZ/pdf"
          },
          "paymentInvoiceWebPayLink": {
            "type": "string",
            "description": "Payable session link for this invoice. The session remains valid until the expiration date, calculated as the payment due date plus the configured grace period. After expiration, the link is no longer payable, and a new session must be generated.",
            "example": "https://example.com/session/ABC012345-6789-01234/pay"
          },
          "paymentCurrencyCode": {
            "type": "string",
            "description": "Currency used for invoice payment.",
            "example": "USD"
          },
          "paymentOrderReference": {
            "type": "string",
            "description": "Order reference used in payment flow.",
            "example": "ABC012345-6789-01234"
          },
          "paymentDueDate": {
            "type": "string",
            "format": "date-time",
            "description": "Business due date for the invoice (ISO 8601). The invoice remains payable via the session until session expiration, which equals the payment due date plus the grace period.",
            "example": "2026-12-01T00:00:00Z"
          },
          "paymentTotals": {
            "$ref": "#/components/schemas/InvoiceTotals"
          },
          "tagsJson": {
            "type": "string",
            "description": "Stringified JSON metadata tags attached to the invoice at creation. Returned verbatim in the POST response.",
            "example": "{\"costCenter\": \"CC-1042\", \"purchaseOrder\": \"PO-2026-Q2-0042\"}"
          }
        }
      },
      "InvoiceRequest": {
        "type": "object",
        "description": "Payload to create and finalize a payment invoice.",
        "required": [
          "currencyCode",
          "contacts",
          "invoiceItems",
          "paymentMethod",
          "mode"
        ],
        "properties": {
          "currencyCode": {
            "type": "string",
            "description": "ISO 4217 currency code used for the invoice.",
            "example": "USD"
          },
          "languageCode": {
            "type": "string",
            "description": "Language/locale code for the invoice.",
            "example": "en"
          },
          "autoConvertPaymentCurrency": {
            "type": "boolean",
            "description": "Whether to auto-convert the currency based on the buyer’s location.",
            "example": true
          },
          "contacts": {
            "type": "array",
            "description": "Billing and delivery contact details for the invoice.",
            "items": {
              "type": "object",
              "required": [
                "contactType",
                "contact"
              ],
              "properties": {
                "contactType": {
                  "type": "string",
                  "description": "Role of the contact in the invoice.",
                  "enum": [
                    "deliverTo",
                    "billTo"
                  ],
                  "example": "deliverTo"
                },
                "contact": {
                  "type": "object",
                  "required": [
                    "email",
                    "firstName",
                    "lastName"
                  ],
                  "properties": {
                    "email": {
                      "type": "string",
                      "description": "Email address of the contact.",
                      "example": "recipient@example.com"
                    },
                    "firstName": {
                      "type": "string",
                      "description": "First name of the contact.",
                      "example": "John"
                    },
                    "lastName": {
                      "type": "string",
                      "description": "Last name of the contact.",
                      "example": "Smith"
                    },
                    "phoneNumber": {
                      "type": "string",
                      "description": "Contact phone number.",
                      "example": "+1 555 000-0001"
                    },
                    "companyName": {
                      "type": "string",
                      "description": "Company name associated with the contact.",
                      "example": "Example Company"
                    },
                    "website": {
                      "type": "string",
                      "description": "Website URL associated with the contact or company.",
                      "example": "https://example.com"
                    },
                    "fullName": {
                      "type": "string",
                      "description": "Optional full name if formatted manually.",
                      "example": "John Smith"
                    }
                  }
                },
                "address": {
                  "type": "object",
                  "properties": {
                    "addressLine1": {
                      "type": "string",
                      "description": "Primary street address.",
                      "example": "123 Main Street"
                    },
                    "addressLine2": {
                      "type": "string",
                      "description": "Additional address info (suite, floor, etc.).",
                      "example": "Suite 100"
                    },
                    "city": {
                      "type": "string",
                      "description": "City or locality.",
                      "example": "Metropolis"
                    },
                    "region": {
                      "type": "string",
                      "description": "State, province, or region.",
                      "example": "CA"
                    },
                    "postalCode": {
                      "type": "string",
                      "description": "Postal or ZIP code.",
                      "example": "12345"
                    },
                    "country": {
                      "type": "string",
                      "description": "Country code in ISO 3166-1 alpha-2 format.",
                      "example": "US"
                    }
                  }
                },
                "organization": {
                  "type": "object",
                  "description": "Organization associated with the contact.",
                  "properties": {
                    "organizationName": {
                      "type": "string",
                      "description": "Legal name of the organization.",
                      "example": "Example Company"
                    },
                    "organizationType": {
                      "type": "string",
                      "description": "Type or classification of the organization.",
                      "example": "S Corporation"
                    },
                    "organizationReference": {
                      "type": "string",
                      "description": "Internal or external reference ID for the organization.",
                      "example": "ORG-001"
                    }
                  }
                }
              }
            }
          },
          "dueDate": {
            "type": "string",
            "format": "date-time",
            "description": "When the invoice is due (ISO 8601 format).",
            "example": "2026-12-01T00:00:00Z"
          },
          "invoiceNote": {
            "type": "string",
            "description": "Optional non-structured note for internal or customer use.",
            "example": "Sample invoice note for reference."
          },
          "invoiceItems": {
            "type": "array",
            "description": "Line items (products/services) included in the invoice.",
            "items": {
              "type": "object",
              "required": [
                "productPath",
                "quantity",
                "useCatalogPricing"
              ],
              "properties": {
                "productPath": {
                  "type": "string",
                  "description": "Product identifier or catalog path.",
                  "example": "sample-product"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Number of units purchased.",
                  "example": 4
                },
                "sku": {
                  "type": "string",
                  "description": "Stock Keeping Unit (SKU) identifier.",
                  "example": "SKU-123"
                },
                "useCatalogPricing": {
                  "type": "boolean",
                  "description": "Whether to use pricing from the catalog.",
                  "example": true
                },
                "display": {
                  "type": "string",
                  "description": "Display name override for this item.",
                  "example": "Sample Product Display Name"
                },
                "summary": {
                  "type": "string",
                  "description": "Short description of the item.",
                  "example": "Short product summary here."
                },
                "extendedItemDescription": {
                  "type": "string",
                  "description": "Additional descriptive text displayed below the corresponding line item on the invoice. Useful for including custom details, disclaimers, or item-specific notes that appear directly beneath the product entry.",
                  "example": "This subscription renews automatically each year unless canceled."
                }
              }
            }
          },
          "paymentMethod": {
            "type": "string",
            "description": "Payment method to use. Common values include CARD, PAYPAL, WIRE, ACH. Refer to FastSpring dashboard for the full list of supported methods.",
            "example": "CARD"
          },
          "mode": {
            "type": "string",
            "description": "Mode used to create the invoice (test or live).",
            "example": "TEST",
            "enum": [
              "TEST",
              "LIVE"
            ]
          },
          "tagsJson": {
            "type": "string",
            "description": "Stringified JSON object of key-value pairs to attach as metadata to this invoice. Values may be strings or arrays of strings. Must be serialized as a JSON string (not a nested object). Stored on the invoice and returned in both POST and GET responses.",
            "example": "{\"costCenter\": \"CC-1042\", \"purchaseOrder\": \"PO-2026-Q2-0042\"}"
          }
        }
      },
      "InvoiceItemResponse": {
        "type": "object",
        "description": "Details of a single item in the invoice.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the invoice item.",
            "example": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U"
          },
          "acquisitionTransactionItemId": {
            "type": "string",
            "description": "ID of the original transaction item.",
            "example": "aB0Cd1E2FGh3Ij-K4LMNop"
          },
          "display": {
            "type": "string",
            "description": "Display name of the item.",
            "example": "Sample Product Display Name"
          },
          "invoiceId": {
            "type": "string",
            "description": "ID of the parent invoice.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "parentId": {
            "type": "string",
            "description": "ID of the parent item, if this is a child item."
          },
          "childItems": {
            "type": "array",
            "description": "Array of any child items (usually empty).",
            "items": {
              "type": "string"
            }
          },
          "productFormat": {
            "type": "string",
            "description": "Format of the product (e.g., DIGITAL, PHYSICAL).",
            "example": "DIGITAL"
          },
          "productPath": {
            "type": "string",
            "description": "Catalog identifier or path for the product.",
            "example": "sample-product"
          },
          "productType": {
            "type": "string",
            "description": "Type of product (BASE, ADD-ON, etc).",
            "example": "BASE"
          },
          "listPrice": {
            "type": "number",
            "description": "Unit price of the item before tax.",
            "example": 25
          },
          "freeTrialPeriod": {
            "type": "string",
            "description": "Free trial duration, if applicable."
          },
          "period": {
            "type": "string",
            "description": "Subscription billing period (e.g., monthly)."
          },
          "periodCount": {
            "type": "integer",
            "description": "Number of billing periods."
          },
          "periodEndDate": {
            "type": "string",
            "format": "date-time",
            "description": "Date when the billing period ends."
          },
          "periodPrice": {
            "type": "number",
            "description": "Price charged for this billing period."
          },
          "shippingPrice": {
            "type": "number",
            "description": "Cost of shipping applied to this item."
          },
          "totalPreShippingPrice": {
            "type": "number",
            "description": "Item total before shipping and tax.",
            "example": 100
          },
          "itemTaxes": {
            "type": "array",
            "description": "Taxes applied to this item.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Tax line ID.",
                  "example": "ABCDEF0G1HI2JK3LMN4OPQRSTU5V"
                },
                "itemId": {
                  "type": "string",
                  "description": "ID of the invoice item this tax applies to.",
                  "example": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U"
                },
                "value": {
                  "type": "number",
                  "description": "Amount of tax applied.",
                  "example": 6
                },
                "taxRate": {
                  "type": "number",
                  "description": "Tax rate percentage.",
                  "example": 6
                },
                "fromGross": {
                  "type": "boolean",
                  "description": "Whether tax was calculated from gross."
                },
                "description": {
                  "type": "string",
                  "description": "Optional tax line description."
                },
                "taxType": {
                  "type": "string",
                  "description": "Type of tax applied.",
                  "example": "US_SALES_TAX"
                }
              }
            }
          },
          "subscription": {
            "type": "boolean",
            "description": "Whether this is a subscription item.",
            "example": false
          },
          "autoRenew": {
            "type": "boolean",
            "description": "If this item auto-renews."
          },
          "taxExempt": {
            "type": "boolean",
            "description": "Whether this item is exempt from tax.",
            "example": false
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the product purchased.",
            "example": 4
          },
          "siblingPosition": {
            "type": "integer",
            "description": "Position of this item relative to others."
          },
          "extendedItemDescription": {
            "type": "string",
            "description": "Detailed description of the item.",
            "example": "Extended product description with additional details."
          }
        }
      },
      "Contact": {
        "type": "object",
        "description": "Contact details used in invoice communication and processing.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique ID for the contact.",
            "example": "ABC0DEFGHI1JKL2MN3OPQRS4TUVW"
          },
          "email": {
            "type": "string",
            "description": "Email address of the contact.",
            "example": "recipient@example.com"
          },
          "firstName": {
            "type": "string",
            "description": "First name of the contact.",
            "example": "John"
          },
          "lastName": {
            "type": "string",
            "description": "Last name of the contact.",
            "example": "Smith"
          },
          "fullName": {
            "type": "string",
            "description": "Full name of the contact (if applicable).",
            "example": "John Smith"
          },
          "companyName": {
            "type": "string",
            "description": "Company name this contact represents.",
            "example": "Example Company"
          },
          "phoneNumber": {
            "type": "string",
            "description": "Contact's phone number.",
            "example": "+1 555 000-0001"
          },
          "website": {
            "type": "string",
            "description": "Website URL for the contact or company.",
            "example": "https://example.com"
          },
          "contactType": {
            "type": "string",
            "description": "Role of the contact in the invoice (e.g., SHIP-TO, BILL-TO, PAYMENT_FACILITATOR).",
            "example": "SHIP-TO"
          },
          "contactServiceId": {
            "type": "string",
            "description": "Optional reference to an external service’s contact ID."
          },
          "contactOrganizationId": {
            "type": "string",
            "description": "Optional reference to an external service’s organization ID."
          }
        }
      },
      "Address": {
        "type": "object",
        "description": "Address details used for invoice delivery and billing.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the address.",
            "example": "ABC0DEFGHIJKL1MNOP23Q4RSTUVW"
          },
          "addressLine1": {
            "type": "string",
            "description": "Primary street address.",
            "example": "123 Main Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "Secondary street address (e.g., suite or unit).",
            "example": "Suite 100"
          },
          "city": {
            "type": "string",
            "description": "City or locality.",
            "example": "Metropolis"
          },
          "region": {
            "type": "string",
            "description": "State, province, or region.",
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "Postal or ZIP code.",
            "example": "12345"
          },
          "country": {
            "type": "string",
            "description": "Country code in ISO 3166-1 alpha-2 format.",
            "example": "US"
          },
          "regionCustom": {
            "type": "string",
            "nullable": true,
            "description": "Custom region string if region is not standardized."
          },
          "addressServiceId": {
            "type": "string",
            "description": "Optional ID from an external address service."
          }
        }
      },
      "InvoiceOrganization": {
        "type": "object",
        "description": "Organization data associated with a contact.",
        "properties": {
          "organizationId": {
            "type": "string",
            "description": "Unique ID for the organization.",
            "example": "ORGABCDEFGHIJKLMNOPQRST0U1VWX"
          },
          "organizationName": {
            "type": "string",
            "description": "Full legal name of the organization.",
            "example": "Example Company"
          },
          "organizationType": {
            "type": "string",
            "description": "Type of business entity.",
            "example": "S Corporation"
          },
          "organizationReference": {
            "type": "string",
            "description": "Optional reference code for this organization.",
            "example": "REF-001"
          }
        }
      },
      "InvoiceTotals": {
        "type": "object",
        "description": "Final calculated totals for the invoice.",
        "properties": {
          "payableTotalDisplay": {
            "type": "string",
            "description": "Formatted string showing final total.",
            "example": "$106.00"
          },
          "subTotalDisplay": {
            "type": "string",
            "description": "Formatted string showing subtotal.",
            "example": "$100.00"
          },
          "discountTotalDisplay": {
            "type": "string",
            "description": "Formatted string showing total discounts.",
            "example": "$0.00"
          },
          "taxTotalDisplay": {
            "type": "string",
            "description": "Formatted string showing total tax.",
            "example": "$6.00"
          },
          "shippingTotalDisplay": {
            "type": "string",
            "description": "Formatted string showing shipping total.",
            "example": "$0.00"
          },
          "taxExemptionReason": {
            "type": "string",
            "description": "Reason for tax exemption if applicable.",
            "example": "US_SALES_TAX"
          },
          "payableTotal": {
            "type": "number",
            "description": "Final amount due.",
            "example": 106
          },
          "subTotal": {
            "type": "number",
            "description": "Subtotal amount before taxes and discounts.",
            "example": 100
          },
          "discountTotal": {
            "type": "number",
            "description": "Total discounts applied.",
            "example": 0
          },
          "taxTotal": {
            "type": "number",
            "description": "Total tax amount.",
            "example": 6
          },
          "feesTotal": {
            "type": "number",
            "description": "Total fees added.",
            "example": 0
          },
          "shippingTotal": {
            "type": "number",
            "description": "Shipping cost.",
            "example": 0
          },
          "incomeTotal": {
            "type": "number",
            "description": "Income total from this invoice.",
            "example": 0
          },
          "payoutFeesTotal": {
            "type": "number",
            "description": "Fees deducted before payout.",
            "example": 0
          },
          "payoutTaxTotal": {
            "type": "number",
            "description": "Taxes deducted before payout.",
            "example": 0
          },
          "payoutTotal": {
            "type": "number",
            "description": "Final payout total.",
            "example": 0
          }
        }
      },
      "InvoiceType": {
        "type": "string",
        "description": "Invoice classification type",
        "enum": [
          "CREDIT_MEMO",
          "INVOICE",
          "FULL_TAX_REFUND",
          "PARTIAL_REFUND",
          "FULL_REFUND",
          "PAYMENT_INVOICE"
        ],
        "example": "INVOICE"
      },
      "InvoiceStatus": {
        "type": "string",
        "description": "Invoice lifecycle status (uppercase).\n- OPEN: Awaiting finalization\n- PENDING: Awaiting payment\n- PAYMENT_ACCEPTED: Payment processing\n- COMPLETE: Fully completed\n- PAID: Legacy status (deprecated, use COMPLETE)\n- CANCELED: Canceled before completion\n- VOID: Voided after completion\n",
        "enum": [
          "CANCELED",
          "OPEN",
          "PAYMENT_ACCEPTED",
          "COMPLETE",
          "PAID",
          "PENDING",
          "VOID"
        ],
        "example": "COMPLETE"
      },
      "ErrorResponseV2": {
        "type": "object",
        "required": [
          "code",
          "message"
        ],
        "properties": {
          "code": {
            "type": "string",
            "description": "Machine-readable error code",
            "example": "VALIDATION_ERROR"
          },
          "message": {
            "type": "string",
            "description": "Human-readable error description",
            "example": "Invalid request parameters"
          }
        }
      }
    }
  }
}
```

Retrieve an invoice

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve an invoice

Retrieves the full details of an invoice using its unique `invoiceId`.

This endpoint returns all invoice metadata, contacts, items, organization info, and calculated financial totals including tax, discount, and shipping.    


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "The Invoices API handles invoice generation, retrieval, and management.\nGenerate invoices for orders and subscriptions, and manage billing documents.\n\n## Authentication\nAll endpoints require HTTP Basic Auth using your API credentials.\n\n\n**Rate Limiting:** All API endpoints are rate limited to 250 requests per IP address per minute. When exceeded, the API returns HTTP 429 with a `Retry-After` header.\n",
    "version": "v1.1.0-kgi27",
    "title": "FastSpring API - Invoices",
    "contact": {
      "name": "FastSpring Developer Support",
      "url": "https://developer.fastspring.com/support",
      "email": "devsupport@fastspring.com"
    }
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
      "name": "Invoices",
      "description": "Create, retrieve, and manage payment invoices with support for contact, address, organization, item, and metadata structures.\n"
    }
  ],
  "paths": {
    "/invoices/{invoiceId}": {
      "get": {
        "summary": "Retrieve an invoice",
        "operationId": "getInvoiceById",
        "tags": [
          "Invoices"
        ],
        "description": "Retrieves the full details of an invoice using its unique `invoiceId`.\n\nThis endpoint returns all invoice metadata, contacts, items, organization info, and calculated financial totals including tax, discount, and shipping.    \n",
        "parameters": [
          {
            "name": "invoiceId",
            "in": "path",
            "required": true,
            "description": "The unique identifier of the invoice to retrieve.",
            "schema": {
              "type": "string",
              "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/InvoiceGetResponse"
                }
              }
            }
          },
          "401": {
            "description": "Authentication credentials are missing or invalid.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
                }
              }
            }
          },
          "404": {
            "description": "Not found",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorNotFoundResponse"
                }
              }
            }
          },
          "429": {
            "description": "Rate limit exceeded. Retry after the number of seconds specified in the Retry-After header.",
            "headers": {
              "Retry-After": {
                "description": "Number of seconds to wait before retrying",
                "schema": {
                  "type": "integer"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
                }
              }
            }
          },
          "500": {
            "description": "Internal server error.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponseV2"
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
      "InvoiceItemResponse": {
        "type": "object",
        "description": "Details of a single item in the invoice.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the invoice item.",
            "example": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U"
          },
          "acquisitionTransactionItemId": {
            "type": "string",
            "description": "ID of the original transaction item.",
            "example": "aB0Cd1E2FGh3Ij-K4LMNop"
          },
          "display": {
            "type": "string",
            "description": "Display name of the item.",
            "example": "Sample Product Display Name"
          },
          "invoiceId": {
            "type": "string",
            "description": "ID of the parent invoice.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "parentId": {
            "type": "string",
            "description": "ID of the parent item, if this is a child item."
          },
          "childItems": {
            "type": "array",
            "description": "Array of any child items (usually empty).",
            "items": {
              "type": "string"
            }
          },
          "productFormat": {
            "type": "string",
            "description": "Format of the product (e.g., DIGITAL, PHYSICAL).",
            "example": "DIGITAL"
          },
          "productPath": {
            "type": "string",
            "description": "Catalog identifier or path for the product.",
            "example": "sample-product"
          },
          "productType": {
            "type": "string",
            "description": "Type of product (BASE, ADD-ON, etc).",
            "example": "BASE"
          },
          "listPrice": {
            "type": "number",
            "description": "Unit price of the item before tax.",
            "example": 25
          },
          "freeTrialPeriod": {
            "type": "string",
            "description": "Free trial duration, if applicable."
          },
          "period": {
            "type": "string",
            "description": "Subscription billing period (e.g., monthly)."
          },
          "periodCount": {
            "type": "integer",
            "description": "Number of billing periods."
          },
          "periodEndDate": {
            "type": "string",
            "format": "date-time",
            "description": "Date when the billing period ends."
          },
          "periodPrice": {
            "type": "number",
            "description": "Price charged for this billing period."
          },
          "shippingPrice": {
            "type": "number",
            "description": "Cost of shipping applied to this item."
          },
          "totalPreShippingPrice": {
            "type": "number",
            "description": "Item total before shipping and tax.",
            "example": 100
          },
          "itemTaxes": {
            "type": "array",
            "description": "Taxes applied to this item.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Tax line ID.",
                  "example": "ABCDEF0G1HI2JK3LMN4OPQRSTU5V"
                },
                "itemId": {
                  "type": "string",
                  "description": "ID of the invoice item this tax applies to.",
                  "example": "AB0CDE1FGHIJKL2M3N4OPQRS5T6U"
                },
                "value": {
                  "type": "number",
                  "description": "Amount of tax applied.",
                  "example": 6
                },
                "taxRate": {
                  "type": "number",
                  "description": "Tax rate percentage.",
                  "example": 6
                },
                "fromGross": {
                  "type": "boolean",
                  "description": "Whether tax was calculated from gross."
                },
                "description": {
                  "type": "string",
                  "description": "Optional tax line description."
                },
                "taxType": {
                  "type": "string",
                  "description": "Type of tax applied.",
                  "example": "US_SALES_TAX"
                }
              }
            }
          },
          "subscription": {
            "type": "boolean",
            "description": "Whether this is a subscription item.",
            "example": false
          },
          "autoRenew": {
            "type": "boolean",
            "description": "If this item auto-renews."
          },
          "taxExempt": {
            "type": "boolean",
            "description": "Whether this item is exempt from tax.",
            "example": false
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the product purchased.",
            "example": 4
          },
          "siblingPosition": {
            "type": "integer",
            "description": "Position of this item relative to others."
          },
          "extendedItemDescription": {
            "type": "string",
            "description": "Detailed description of the item.",
            "example": "Extended product description with additional details."
          }
        }
      },
      "Contact": {
        "type": "object",
        "description": "Contact details used in invoice communication and processing.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique ID for the contact.",
            "example": "ABC0DEFGHI1JKL2MN3OPQRS4TUVW"
          },
          "email": {
            "type": "string",
            "description": "Email address of the contact.",
            "example": "recipient@example.com"
          },
          "firstName": {
            "type": "string",
            "description": "First name of the contact.",
            "example": "John"
          },
          "lastName": {
            "type": "string",
            "description": "Last name of the contact.",
            "example": "Smith"
          },
          "fullName": {
            "type": "string",
            "description": "Full name of the contact (if applicable).",
            "example": "John Smith"
          },
          "companyName": {
            "type": "string",
            "description": "Company name this contact represents.",
            "example": "Example Company"
          },
          "phoneNumber": {
            "type": "string",
            "description": "Contact's phone number.",
            "example": "+1 555 000-0001"
          },
          "website": {
            "type": "string",
            "description": "Website URL for the contact or company.",
            "example": "https://example.com"
          },
          "contactType": {
            "type": "string",
            "description": "Role of the contact in the invoice (e.g., SHIP-TO, BILL-TO, PAYMENT_FACILITATOR).",
            "example": "SHIP-TO"
          },
          "contactServiceId": {
            "type": "string",
            "description": "Optional reference to an external service’s contact ID."
          },
          "contactOrganizationId": {
            "type": "string",
            "description": "Optional reference to an external service’s organization ID."
          }
        }
      },
      "Address": {
        "type": "object",
        "description": "Address details used for invoice delivery and billing.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the address.",
            "example": "ABC0DEFGHIJKL1MNOP23Q4RSTUVW"
          },
          "addressLine1": {
            "type": "string",
            "description": "Primary street address.",
            "example": "123 Main Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "Secondary street address (e.g., suite or unit).",
            "example": "Suite 100"
          },
          "city": {
            "type": "string",
            "description": "City or locality.",
            "example": "Metropolis"
          },
          "region": {
            "type": "string",
            "description": "State, province, or region.",
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "Postal or ZIP code.",
            "example": "12345"
          },
          "country": {
            "type": "string",
            "description": "Country code in ISO 3166-1 alpha-2 format.",
            "example": "US"
          },
          "regionCustom": {
            "type": "string",
            "nullable": true,
            "description": "Custom region string if region is not standardized."
          },
          "addressServiceId": {
            "type": "string",
            "description": "Optional ID from an external address service."
          }
        }
      },
      "InvoiceOrganization": {
        "type": "object",
        "description": "Organization data associated with a contact.",
        "properties": {
          "organizationId": {
            "type": "string",
            "description": "Unique ID for the organization.",
            "example": "ORGABCDEFGHIJKLMNOPQRST0U1VWX"
          },
          "organizationName": {
            "type": "string",
            "description": "Full legal name of the organization.",
            "example": "Example Company"
          },
          "organizationType": {
            "type": "string",
            "description": "Type of business entity.",
            "example": "S Corporation"
          },
          "organizationReference": {
            "type": "string",
            "description": "Optional reference code for this organization.",
            "example": "REF-001"
          }
        }
      },
      "InvoiceGetResponse": {
        "type": "object",
        "description": "Full response returned when retrieving an existing invoice by ID.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the invoice.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "acquisitionTransactionId": {
            "type": "string",
            "description": "Identifier of the acquisition transaction.",
            "example": "ABCD0EF1GHIJKLMNOPQRSTUVWXYZ"
          },
          "acquisitionReturnTransactionId": {
            "type": "string",
            "description": "ID of the return transaction, if applicable."
          },
          "bankPaymentInstruction": {
            "type": "string",
            "description": "Bank instructions for manual payment (if any)."
          },
          "companyName": {
            "type": "string",
            "description": "Company that owns the invoice.",
            "example": "recipient@example.com"
          },
          "createdOn": {
            "type": "string",
            "format": "date-time",
            "description": "ISO 8601 timestamp of when the invoice was created.",
            "example": "2026-04-02T16:08:24Z"
          },
          "currency": {
            "type": "string",
            "description": "ISO 4217 currency code.",
            "example": "USD"
          },
          "customerReference": {
            "type": "string",
            "description": "Optional reference code from the customer."
          },
          "dueDate": {
            "type": "string",
            "format": "date-time",
            "description": "Due date for the invoice.",
            "example": "2026-12-31T00:00:00Z"
          },
          "eguiNumber": {
            "type": "string",
            "description": "EGUI number for tax compliance (if applicable)."
          },
          "items": {
            "type": "array",
            "description": "List of items in the invoice.",
            "items": {
              "$ref": "#/components/schemas/InvoiceItemResponse"
            }
          },
          "status": {
            "$ref": "#/components/schemas/InvoiceStatus"
          },
          "language": {
            "type": "string",
            "description": "Language code used for the invoice.",
            "example": "en"
          },
          "invoiceOrderType": {
            "type": "string",
            "description": "The type of order, if specified."
          },
          "invoiceSecret": {
            "type": "string",
            "description": "Secure token for viewing the invoice.",
            "example": "TAX01ABCDEFGHIJ2KLMNOP3QRSTUV4WXYZABC5DEFGHIJKLMNOPQ"
          },
          "invoiceType": {
            "$ref": "#/components/schemas/InvoiceType"
          },
          "netTermsDays": {
            "type": "integer",
            "description": "Net terms in days, if defined.",
            "example": 10
          },
          "orderApprovalType": {
            "type": "string",
            "description": "Indicates the invoice's approval mechanism."
          },
          "orderIsResumable": {
            "type": "boolean",
            "description": "Indicates if the order is resumable."
          },
          "orderReference": {
            "type": "string",
            "description": "Human-readable reference for the order.",
            "example": "ABC012345-6789-01234"
          },
          "paymentDate": {
            "type": "string",
            "format": "date-time",
            "description": "Date payment was received according to rfc3339 https://www.rfc-editor.org/rfc/rfc3339#section-5.6.",
            "example": "2026-12-31T23:10:30Z"
          },
          "paymentReceiverAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "paymentReceiver": {
            "$ref": "#/components/schemas/Contact"
          },
          "purchaser": {
            "$ref": "#/components/schemas/Contact"
          },
          "purchaserAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "purchaserOrganization": {
            "$ref": "#/components/schemas/InvoiceOrganization"
          },
          "receiver": {
            "$ref": "#/components/schemas/Contact"
          },
          "receiverAddress": {
            "$ref": "#/components/schemas/Address"
          },
          "receiverOrganization": {
            "$ref": "#/components/schemas/InvoiceOrganization"
          },
          "refunds": {
            "type": "array",
            "description": "List of any refunds applied to the invoice.",
            "items": {
              "type": "string"
            },
            "example": []
          },
          "reminderDate": {
            "type": "string",
            "format": "date-time",
            "description": "Date a reminder was sent (if applicable)."
          },
          "shippingTotal": {
            "type": "number",
            "description": "Total shipping cost.",
            "example": 0
          },
          "subTotal": {
            "type": "number",
            "description": "Subtotal before tax and discounts.",
            "example": 59.8
          },
          "siteId": {
            "type": "string",
            "description": "FastSpring site identifier.",
            "example": "ABC0DE1GHIJ2"
          },
          "siteMainUrl": {
            "type": "string",
            "description": "Main URL for the site."
          },
          "siteSupportEmail": {
            "type": "string",
            "description": "Support email address for the store."
          },
          "taxExemptionId": {
            "type": "string",
            "description": "Tax exemption ID (if applicable)."
          },
          "taxRate": {
            "type": "number",
            "description": "Tax rate applied to the invoice.",
            "example": 6
          },
          "taxType": {
            "type": "string",
            "description": "Type of tax applied to the invoice.",
            "example": "US_SALES_TAX"
          },
          "totalOrderValue": {
            "type": "number",
            "description": "Final amount due for the order.",
            "example": 63.39
          },
          "totalDiscountValue": {
            "type": "number",
            "description": "Total discount amount.",
            "example": 0
          },
          "totalListPriceValue": {
            "type": "number",
            "description": "Total price before any discounting."
          },
          "totalTaxValue": {
            "type": "number",
            "description": "Total tax amount applied.",
            "example": 3.59
          },
          "updatedOn": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp for when the invoice was last updated."
          },
          "version": {
            "type": "string",
            "description": "Version of the invoice schema.",
            "example": "2"
          },
          "notes": {
            "type": "string",
            "description": "Freeform notes for the invoice.",
            "example": "Add notes here."
          },
          "tagsJson": {
            "type": "string",
            "description": "Stringified JSON metadata tags attached to the invoice. Returned in GET responses if set at creation.",
            "example": "{\"costCenter\": \"CC-1042\", \"purchaseOrder\": \"PO-2026-Q2-0042\"}"
          }
        }
      },
      "ErrorNotFoundResponse": {
        "type": "object",
        "description": "Standard error response for a 404 Not Found status.",
        "properties": {
          "status": {
            "type": "string",
            "description": "Status identifier for the error.",
            "example": "NOT_FOUND"
          },
          "timestamp": {
            "type": "string",
            "format": "date-time",
            "description": "Time when the error occurred (ISO 8601 format).",
            "example": "2026-04-02T16:15:11.806Z"
          },
          "id": {
            "type": "string",
            "description": "Unique trace ID for debugging and request tracking.",
            "example": "FS4JB3YL2V3VDQ5BY34VMWIELFB4;Self=1-67ed628f-650c38ea30e13c5a1f590ede;Root=1-67ed628f-7d5aa9e341f8441975ed2089"
          },
          "message": {
            "type": "string",
            "description": "Summary of the error.",
            "example": "Resource does not exist."
          },
          "details": {
            "type": "string",
            "description": "Additional details about the error.",
            "example": "Invoice was not found for parameters {id=ABCD0EF1GHIJKLMNOPQRSTUVWXYZ}"
          }
        }
      },
      "InvoiceType": {
        "type": "string",
        "description": "Invoice classification type",
        "enum": [
          "CREDIT_MEMO",
          "INVOICE",
          "FULL_TAX_REFUND",
          "PARTIAL_REFUND",
          "FULL_REFUND",
          "PAYMENT_INVOICE"
        ],
        "example": "INVOICE"
      },
      "InvoiceStatus": {
        "type": "string",
        "description": "Invoice lifecycle status (uppercase).\n- OPEN: Awaiting finalization\n- PENDING: Awaiting payment\n- PAYMENT_ACCEPTED: Payment processing\n- COMPLETE: Fully completed\n- PAID: Legacy status (deprecated, use COMPLETE)\n- CANCELED: Canceled before completion\n- VOID: Voided after completion\n",
        "enum": [
          "CANCELED",
          "OPEN",
          "PAYMENT_ACCEPTED",
          "COMPLETE",
          "PAID",
          "PENDING",
          "VOID"
        ],
        "example": "COMPLETE"
      },
      "ErrorResponseV2": {
        "type": "object",
        "required": [
          "code",
          "message"
        ],
        "properties": {
          "code": {
            "type": "string",
            "description": "Machine-readable error code",
            "example": "VALIDATION_ERROR"
          },
          "message": {
            "type": "string",
            "description": "Human-readable error description",
            "example": "Invalid request parameters"
          }
        }
      }
    }
  }
}
```