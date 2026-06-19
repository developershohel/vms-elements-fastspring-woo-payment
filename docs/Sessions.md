i add session v1 and v2 both. if v2 cover v1 than only add v2
Sessions v1

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Sessions v1

> **Heads up:** Sessions v1 is the legacy version of the Sessions API and is maintained for existing integrations only. **If you're starting a new integration, use the current [Sessions API](/reference/sessions-overview).** The current API exposes dedicated endpoints for managing cart items and customer details independently, giving you finer control over the checkout flow without replacing the full session payload on every update.

<div class="spacer-sm" />

Use the Sessions v1 API to create custom order sessions and pass pre-configured cart data to your checkout before a buyer checks out. Sessions let you apply coupons, override product pricing, attach custom tags, and pre-fill customer details — all server-side, before the buyer sees the page.

Once created, a session returns a session ID you use to route the buyer directly into a pre-filled checkout. For base URL, authentication, technical standards, and observability, see the [API overview](/reference/getting-started-with-your-api).

<div class="spacer-md" />

## API reference

The Sessions v1 API exposes a single endpoint for creating order sessions. Select it below to view request parameters and response schemas.

<div class="spacer-sm" />

<Cards columns={1}>
  <Card title="Create session" icon="fa-circle-plus" iconColor="#38a169" href="/reference/createordersession">
    Create a custom order session with product items, pricing overrides, coupons, tags, and customer details.
  </Card>
</Cards>

<div class="spacer-md" />

## Session configuration

A session request supports several optional configurations. These can be combined in a single request.

<div class="spacer-sm" />

<Accordion title="Customer identification" icon="fa-user" iconColor="#3182ce">
  <div class="spacer-sm" />

Identify the buyer using one of two methods:

* **Account ID** — Pass the FastSpring-generated `account` ID for an existing customer. FastSpring applies all account details to the session automatically.
* **Contact object** — Pass inline contact information (name, email, company, phone, country, language) when an account ID is unavailable. If an account already exists for the provided email, FastSpring associates it with the session.

> **Note:** Always include a `country` value in the `contact` object for new buyers. If omitted, FastSpring defaults to the United States and USD as the transaction currency.

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Price overrides" icon="fa-tag" iconColor="#805ad5">
  <div class="spacer-sm" />

Override catalog pricing for one or more items in the session by passing a `pricing` object on each item. You can override:

* **Unit price** — Specify per-currency amounts using ISO 4217 currency codes (e.g., `USD`, `EUR`).
* **Trial behavior** — Set a trial length in days, specify whether payment is collected during the trial (`paymentCollected`), and define whether the trial is paid or free (`paidTrial`).
* **Renewal logic** — Control post-trial or post-term behavior using the `renew` field: `auto`, `manual`, `opt-auto`, or `opt-manual`.
* **Discounts** — Apply percentage or flat discounts with `discountType`, `discountDuration`, and `quantityDiscounts`.

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Coupons" icon="fa-ticket" iconColor="#e53e3e">
  <div class="spacer-sm" />

Apply a discount code to the session order by passing a `coupon` field with a valid coupon code (not the coupon ID).

```json
{
  "account": "abCdE1FGH2Hij3KLMnOpqR",
  "coupon": "10OFF",
  "items": [
    { "product": "basic-laptop", "quantity": 1 }
  ]
}
```

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Tags and attributes" icon="fa-tags" iconColor="#ff9950">
  <div class="spacer-sm" />

Attach metadata to the order or individual items using key-value pairs:

* **Order-level tags** — Use the `tags` object to attach custom labels for categorization, reporting, or integration workflows.
* **Item-level attributes** — Use the `attributes` object on any line item for product-specific metadata or segmentation logic.

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Session expiration" icon="fa-clock" iconColor="#718096">
  <div class="spacer-sm" />

Sessions are valid for **24 hours** by default. To extend this window, pass an `expiration` value of up to **7 days**.

```json
{
  "account": "abCdE1FGH2Hij3KLMnOpqR",
  "expiration": 7,
  "items": [
    { "product": "prime-sub", "quantity": 1 }
  ]
}
```

  <div class="spacer-sm" />
</Accordion>

<div class="spacer-md" />

## Collect payment

After creating a session, use the returned session ID to route the buyer into a pre-filled checkout. The method depends on your checkout type.

<div class="spacer-sm" />

<Cards columns={2}>
  <Card title="Web checkout" icon="fa-globe" iconColor="#3182ce">
    Append `/session/` and the session ID to your checkout URL:

```
`https://yourstore.onfastspring.com/session/{sessionId}`
```

  </Card>

  <Card title="Popup checkout" icon="fa-window-restore" iconColor="#805ad5">
    Pass the session ID into the `fastspring.builder.checkout` method to launch the pre-filled popup checkout.
  </Card>
</Cards>

> **Note:** Customers cannot modify session data. FastSpring applies all session configuration automatically when the buyer selects a payment method.
Create a session

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create a session

Creates custom order sessions with various configurations, such as single product sessions, price overrides, coupons, and custom tags.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Sessions",
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
      "name": "Sessions",
      "description": "Create a custom order session with various configurations, such as single product sessions, price overrides, coupons, and custom tags.\n"
    }
  ],
  "paths": {
    "/sessions": {
      "post": {
        "summary": "Create a session",
        "tags": [
          "Sessions"
        ],
        "operationId": "CreateOrderSession",
        "deprecated": false,
        "description": "Creates custom order sessions with various configurations, such as single product sessions, price overrides, coupons, and custom tags.\n",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateSessionRequest"
              },
              "examples": {
                "CreateSingleProductSession": {
                  "summary": "Create a custom order session with a single product",
                  "description": "Creates an order session for a single product using an existing account ID.\n",
                  "value": {
                    "account": "abCdE1FGH2Hij3KLMnOpqR",
                    "items": [
                      {
                        "product": "basic-laptop",
                        "quantity": 1
                      }
                    ]
                  }
                },
                "OverridePrice": {
                  "summary": "Override the price of a product",
                  "description": "Creates a session to override a product's price using contact information instead of a known account ID.\n",
                  "value": {
                    "contact": {
                      "first": "Jane",
                      "last": "Doe",
                      "email": "jane.doe@example.com",
                      "company": "TechCorp",
                      "phone": "1234567890",
                      "country": "US",
                      "language": "en"
                    },
                    "lookup": {
                      "custom": "customKey123"
                    },
                    "items": [
                      {
                        "product": "basic-laptop",
                        "quantity": 1,
                        "pricing": {
                          "price": {
                            "USD": 399.99
                          }
                        }
                      }
                    ]
                  }
                },
                "ApplyCoupon": {
                  "summary": "Apply a coupon to an order",
                  "description": "Creates a session to apply a valid coupon code to an order using a known account ID.\n",
                  "value": {
                    "account": "abCdE1FGH2Hij3KLMnOpqR",
                    "items": [
                      {
                        "product": "basic-laptop",
                        "quantity": 2
                      }
                    ],
                    "coupon": "10OFF"
                  }
                },
                "UpdateSessionTagsAndAttributes": {
                  "summary": "Update custom tags, product attributes, and discounts",
                  "description": "Creates a session to update custom tags, product attributes, and pricing discounts for an order.\n",
                  "value": {
                    "account": "abCdE1FGH2Hij3KLMnOpqR",
                    "tags": {
                      "TagKey1": "TagValue1",
                      "TagKey2": "TagValue2"
                    },
                    "items": [
                      {
                        "product": "product-path",
                        "attributes": {
                          "AttributeKey1": "AttributeValue1",
                          "AttributeKey2": "AttributeValue2"
                        }
                      },
                      {
                        "product": "some-monthly-subscription",
                        "quantity": 1,
                        "pricing": {
                          "trial": 0,
                          "paymentCollected": true,
                          "paidTrial": false,
                          "renew": "auto",
                          "interval": "month",
                          "intervalLength": 1,
                          "quantityBehavior": "allow",
                          "quantityDefault": 1,
                          "price": {
                            "USD": 399.99,
                            "EUR": 384.36
                          },
                          "discountType": "percent",
                          "discountDuration": 2,
                          "quantityDiscounts": {
                            "1": 50
                          }
                        },
                        "attributes": {
                          "ATTRIBUTE1": "value1"
                        }
                      }
                    ]
                  }
                }
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
                  "$ref": "#/components/schemas/CreateOrderSessionResponse"
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
      "CreateSessionRequest": {
        "type": "object",
        "description": "Request schema for creating a custom order session.\n",
        "properties": {
          "account": {
            "type": "string",
            "description": "FastSpring-generated customer account ID. Required for sessions that use an existing account.\n",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "contact": {
            "type": "object",
            "description": "Contact information for the customer. Used when an `account` ID is not provided.\n\nIf an account already exists for this email, FastSpring will associate it with the session and may update the account details.\n",
            "properties": {
              "first": {
                "type": "string",
                "description": "Customer's first name.",
                "example": "Jane"
              },
              "last": {
                "type": "string",
                "description": "Customer's last name.",
                "example": "Doe"
              },
              "email": {
                "type": "string",
                "format": "email",
                "description": "Customer's email address.\nIf an account already exists with this email, it will be associated with this session.\n",
                "example": "jane.doe@example.com"
              },
              "company": {
                "type": "string",
                "description": "Customer's company name, if any.",
                "example": "TechCorp"
              },
              "phone": {
                "type": "string",
                "description": "Customer's phone number, if any.",
                "example": "1234567890"
              },
              "country": {
                "type": "string",
                "description": "ISO 3166-1 alpha-2 country code.\n\nInclude the `country` field in the contact object for new buyers; if this field is missing, the system will default to the United States and USD as currency.\n\nFor known users, the checkout will redirect to the account's registered country.\n",
                "example": "US"
              },
              "language": {
                "type": "string",
                "description": "ISO 639-1 language code for the buyer's preferred language.",
                "example": "en"
              }
            }
          },
          "lookup": {
            "type": "object",
            "description": "Optional lookup configuration for resolving or linking accounts using a custom key.\n",
            "properties": {
              "custom": {
                "type": "string",
                "description": "Alphanumeric string (A–Z, a–z, 0–9, underscore, or hyphen) with a minimum length of 4.\n\nUsed as a unique lookup phrase for searching or linking accounts.\n",
                "example": "customKey123"
              }
            }
          },
          "tags": {
            "type": "object",
            "description": "Order-level tags as key-value pairs. These can be used for categorization, reporting, or integration workflows.\n",
            "additionalProperties": {
              "type": "string",
              "description": "Value of the custom tag."
            },
            "example": {
              "TagKey1": "TagValue1",
              "TagKey2": "TagValue2"
            }
          },
          "coupon": {
            "type": "string",
            "description": "Valid coupon code (not coupon ID) applied to the order.",
            "example": "10OFF"
          },
          "items": {
            "type": "array",
            "description": "One or more items to include in the session. Each item can represent a product, subscription, or advanced configuration (such as price overrides and custom attributes).\n",
            "items": {
              "type": "object",
              "required": [
                "product"
              ],
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product path of the item to be included in the order.",
                  "example": "basic-laptop"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product to be included in the order.",
                  "example": 1
                },
                "attributes": {
                  "type": "object",
                  "description": "Custom attributes associated with the product in this session. Use these for metadata, segmentation, or integration logic.\n",
                  "additionalProperties": {
                    "type": "string",
                    "description": "Value of the custom attribute."
                  },
                  "example": {
                    "AttributeKey1": "AttributeValue1",
                    "AttributeKey2": "AttributeValue2"
                  }
                },
                "pricing": {
                  "type": "object",
                  "description": "Pricing details for the item. Use this object when you need to override catalog pricing or apply custom trial and discount behavior for the session.\n",
                  "properties": {
                    "trial": {
                      "type": "integer",
                      "description": "Trial period in days. Must be greater than 0 to enable a trial for this item.\n",
                      "example": 7
                    },
                    "paymentCollected": {
                      "type": "boolean",
                      "description": "Indicates if payment is collected during the trial period.\n",
                      "example": true
                    },
                    "paidTrial": {
                      "type": "boolean",
                      "description": "Specifies whether the trial period is paid (`true`) or free (`false`).\n",
                      "example": false
                    },
                    "trialPrice": {
                      "type": "object",
                      "description": "Price during the trial period. Required if `paidTrial` is `true`; ignored if `paidTrial` is `false`.\n",
                      "additionalProperties": {
                        "type": "string",
                        "description": "Price amount in the specified currency."
                      },
                      "example": {
                        "USD": 5,
                        "EUR": 4
                      }
                    },
                    "renew": {
                      "type": "string",
                      "description": "Renewal behavior after the initial term or trial.\n\nSupported values:\n- `auto` – Automatically rebills at renewal.\n- `manual` – Buyer must manually renew.\n- `opt-auto` – Buyer can opt in to automatic rebilling.\n- `opt-manual` – Buyer can opt in to manual renewal.\n",
                      "example": "auto",
                      "enum": [
                        "auto",
                        "manual",
                        "opt-auto",
                        "opt-manual"
                      ]
                    },
                    "interval": {
                      "type": "string",
                      "description": "Billing interval for the subscription.\n",
                      "example": "month",
                      "enum": [
                        "adhoc",
                        "day",
                        "week",
                        "month",
                        "year"
                      ]
                    },
                    "intervalLength": {
                      "type": "integer",
                      "description": "Length of the billing interval. Required if `interval` is specified and is not `adhoc`.\n",
                      "example": 1
                    },
                    "quantityBehavior": {
                      "type": "string",
                      "description": "Behavior for quantity selection.\n",
                      "example": "allow",
                      "enum": [
                        "allow",
                        "lock",
                        "hide"
                      ]
                    },
                    "quantityDefault": {
                      "type": "integer",
                      "description": "Default quantity for the product. Must be a positive integer.",
                      "example": 1
                    },
                    "price": {
                      "type": "object",
                      "description": "Price of the product in various currencies.\n\nEach key is a 3-letter currency code, and the value is the price amount in that currency.\n",
                      "additionalProperties": {
                        "type": "string",
                        "description": "Price amount in the specified currency."
                      },
                      "example": {
                        "USD": 399.99,
                        "EUR": 384.36
                      }
                    },
                    "discountType": {
                      "type": "string",
                      "description": "Type of discount applied when `price` or `quantityDiscounts` is configured.\n",
                      "example": "percent",
                      "enum": [
                        "percent",
                        "amount"
                      ]
                    },
                    "discountDuration": {
                      "type": "integer",
                      "description": "Duration of the discount in billing intervals. After this period, standard pricing applies.\n",
                      "example": 2
                    },
                    "quantityDiscounts": {
                      "type": "object",
                      "description": "Quantity-based discounts. Keys represent the quantity threshold, and values represent either:\n- A number (percentage) when `discountType` is `percent`.\n- A currency-to-amount map when `discountType` is `amount`.\n",
                      "additionalProperties": {
                        "oneOf": [
                          {
                            "type": "number",
                            "description": "Percentage discount if `discountType` is `percent`."
                          },
                          {
                            "type": "object",
                            "description": "Fixed amount discount per currency if `discountType` is `amount`.",
                            "additionalProperties": {
                              "type": "number",
                              "description": "Discount amount in the specified currency."
                            }
                          }
                        ]
                      },
                      "example": {
                        "1": 50
                      }
                    }
                  }
                }
              }
            }
          }
        },
        "required": [
          "items"
        ]
      },
      "CreateOrderSessionResponse": {
        "type": "object",
        "description": "Response returned after creating an order session.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the order session.",
            "example": "gNFgV9ITTbyylJkiIhnmOQ"
          },
          "currency": {
            "type": "string",
            "description": "Currency code for the order session.\nThe currency you specify in the request payload is localized to match the session country's currency when applicable.\n",
            "example": "USD"
          },
          "expires": {
            "type": "integer",
            "description": "Expiration timestamp for the order session in milliseconds since the Unix epoch.\nAfter this timestamp, the order session is no longer valid.\n",
            "example": 1731469407156
          },
          "order": {
            "type": "string",
            "description": "Identifier for the created order. If no order has been created yet, this field is null.\n",
            "nullable": true,
            "example": null
          },
          "account": {
            "type": "string",
            "description": "Unique identifier for the account associated with the order session.",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "subtotal": {
            "type": "number",
            "format": "float",
            "description": "Subtotal amount for the order session.",
            "example": 399.99
          },
          "items": {
            "type": "array",
            "description": "Items included in the order session.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product path of the product included in the order.",
                  "example": "basic-laptop"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Number of units of the product being ordered.",
                  "example": 1
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

session v2
Sessions

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Sessions

Use the Sessions API to programmatically create and manage checkout sessions, passing pre-filled cart and customer data directly to your storefront before the buyer ever sees the page.

The Sessions API exposes dedicated endpoints for managing individual cart items and customer details — enabling dynamic checkout flows without replacing the entire session payload. For base URL, authentication, technical standards, and observability, see the [API overview](/reference/api-overview).

> **Note:** Maintaining an existing integration on the legacy API? See the [Sessions v1 reference](/reference/sessions-v1-overview). All new integrations should use the current Sessions API documented here.

<div class="spacer-md" />

## API reference

The Sessions API is organized around the order session lifecycle — from creation to cart management and customer updates. Select an endpoint below to view its request parameters, path variables, and response schemas.

<div class="spacer-sm" />

<Cards columns={4}>
  <Card title="Create session" icon="fa-circle-plus" iconColor="#38a169" href="/reference/createsession">
    Create a new order session with pre-filled cart items, customer details, and optional custom pricing.
  </Card>

  <Card title="Retrieve session" icon="fa-magnifying-glass" iconColor="#3182ce" href="/reference/retrievesession">
    Retrieve the complete details and current state of an existing order session.
  </Card>

  <Card title="Update session" icon="fa-pen-to-square" iconColor="#805ad5" href="/reference/updatesession">
    Overwrite customer details, items, and custom pricing on an existing session.
  </Card>

  <Card title="Retrieve payment methods" icon="fa-credit-card" iconColor="#319795" href="/reference/retrievepaymentmethods">
    Retrieve the localized, filtered list of payment methods available for a session.
  </Card>

  <Card title="Add session item" icon="fa-cart-plus" iconColor="#ff9950" href="/reference/addsessionitem">
    Append a new product item to the cart of an existing session.
  </Card>

  <Card title="Remove session item" icon="fa-trash-can" iconColor="#e53e3e" href="/reference/removesessionitem">
    Remove a specific product from the session cart by product path.
  </Card>

  <Card title="Update session item" icon="fa-pen" iconColor="#ecc94b" href="/reference/updatesessionitem">
    Modify the quantity or custom pricing of one or more products in the session cart.
  </Card>

  <Card title="Update session customer" icon="fa-user-pen" iconColor="#4a5568" href="/reference/updatesessioncustomer">
    Update customer and billing information on an existing session without replacing the full payload.
  </Card>
</Cards>

<div class="spacer-md" />

## Checkout path

Every Sessions API endpoint requires a `checkoutPath` as a path parameter. The checkout path identifies the target checkout instance and ensures sessions are created with the correct pricing, configuration, and buyer experience.

<div class="spacer-sm" />

<Cards columns={1}>
  <Card title="Checkout path format" icon="fa-sitemap" iconColor="#805ad5">
    The `checkoutPath` is composed of two segments separated by a single forward slash: **`{storeId}/{checkoutId}`**.

````
* **`storeId`** — your FastSpring store identifier (for example, `examplestore`).
* **`checkoutId`** — the identifier of a specific checkout configured in that store (for example, `popup-checkout`).

Combined, those values produce a `checkoutPath` of `examplestore/popup-checkout`, which slots into the request URL where `{checkoutPath}` appears:

```
https://api.fastspring.com/v2/checkouts/{checkoutPath}/sessions
```

With the example values above, the resolved URL is:

```
https://api.fastspring.com/v2/checkouts/examplestore/popup-checkout/sessions
```


**How `checkoutId` is constructed**

A `checkoutId` is generated by FastSpring when you create a checkout in your store, and it always follows the pattern **`{checkoutType}-{checkoutName}`**:

* **`checkoutType`** — one of FastSpring's supported checkout types: `popup`, `embedded`, or `web`. This prefix is fixed by the platform based on the type of checkout you created — it is **not** a value you choose.
* **`checkoutName`** — the name you gave the checkout when you created it (for example, `checkout`, `dark-theme`, `default-template`, `holiday-promo`).

Stores frequently run multiple checkout variations simultaneously, so always target the specific `checkoutId` for the experience you want to load — using the wrong one will return session data with the wrong pricing, locale, or layout configuration.
````

  </Card>
</Cards>
Create session

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create session

Creates a new order session. Accepts customer details, line items, and custom pricing. Server-to-server authentication is required to modify specific restricted fields.

The 201 response includes the session `id`, a ready-to-use `checkoutUrls.webcheckoutUrl` to redirect the buyer, calculated cart totals, and a `checkoutStatus` indicating whether the session is ready for checkout.

> 📘 No follow-up GET required
>
> Everything needed to launch the buyer into checkout — including the session ID — is returned directly in this response. Use `checkoutUrls.webcheckoutUrl` to redirect the buyer, or pass the `id` to your frontend integration.

<br />

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions": {
      "post": {
        "tags": [
          "Session"
        ],
        "summary": "Create session",
        "description": "Creates a new order session. Accepts customer details, line items, and custom pricing. Server-to-server authentication is required to modify specific restricted fields.",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          }
        ],
        "operationId": "createSession",
        "security": [
          {
            "none": [
              "NONE"
            ]
          },
          {
            "basicAuth": [
              "ROLE_SELLER_API_READ_WRITE"
            ]
          },
          {
            "oauth2": [
              "ROLE_SELLER_API_READ_WRITE",
              "ROLE_SESSION_SERVICE_WRITE"
            ]
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateSessionRequest"
              },
              "examples": {
                "AllFields": {
                  "$ref": "#/components/examples/AllFields"
                },
                "MinFields": {
                  "$ref": "#/components/examples/MinFields"
                }
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Successfully created the session.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/BaseSessionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates missing required fields or invalid formatting.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
                }
              }
            }
          },
          "401": {
            "description": "Unauthorized. Access is restricted due to invalid authentication or location settings.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "CreateSessionRequest": {
        "type": "object",
        "description": "The primary request payload used to formulate a new order session.",
        "properties": {
          "locale": {
            "type": "string",
            "description": "Set the language for the buyer's experience. Accepts standard 2-letter language codes (e.g., `en`). Defaults to the browser locale if omitted.",
            "example": "en",
            "maxLength": 5
          },
          "country": {
            "type": "string",
            "description": "The 2-letter ISO country code defining the buyer's billing location. Inferred from `buyerIp` if omitted.",
            "example": "US",
            "pattern": "^[A-Za-z]{2}$"
          },
          "buyerIp": {
            "type": "string",
            "description": "The IPv4 or IPv6 address of the buyer. Used to infer `country` and `currency` if they are omitted. When executing an authenticated server-to-server request, this field acts as an override.",
            "example": "127.0.0.1",
            "maxLength": 39
          },
          "live": {
            "type": "boolean",
            "description": "Indicate whether the session processes in live mode (`true`) or test mode (`false`).\nReview the [Test orders](https://developer.fastspring.com/docs/test-orders) documentation to learn how to safely simulate the buyer experience using test credit cards.\n> **Note:** If the targeted checkout is set to 'test mode' in your checkout settings, this value automatically defaults to `false` regardless of what you pass in the request.\n",
            "default": true,
            "example": true
          },
          "customer": {
            "$ref": "#/components/schemas/CustomerRequest"
          },
          "orderTags": {
            "type": "object",
            "description": "A key-value map of custom metadata attached to the finalized order record. Requires an authenticated request.",
            "example": {
              "campaign": "Q1_Social"
            }
          },
          "cart": {
            "$ref": "#/components/schemas/CartRequest"
          },
          "paymentMethodsOrder": {
            "type": "array",
            "description": "Dictates the explicit sorting order of payment methods presented to the buyer. Unsupported payment methods are automatically suppressed. Requires an authenticated request.",
            "example": [
              "CARD",
              "PAYPAL"
            ],
            "items": {
              "$ref": "#/components/schemas/PaymentMethodType"
            }
          },
          "hidePaymentMethods": {
            "type": "array",
            "description": "Explicitly filters the provided payment methods from the buyer's interface. Requires an authenticated request.",
            "example": [
              "WIRE"
            ],
            "items": {
              "$ref": "#/components/schemas/PaymentMethodType"
            }
          }
        },
        "required": [
          "checkoutPath"
        ]
      },
      "PaymentMethodType": {
        "type": "string",
        "description": "Identifies the specific payment method brand or channel.",
        "enum": [
          "ACH",
          "ALIPAY",
          "AMAZON",
          "APPLE_PAY",
          "CARD",
          "GOOGLE_PAY",
          "IDEAL",
          "KAKAOPAY",
          "KLARNA",
          "MERCADO_PAGO",
          "PAYPAL",
          "PIX",
          "SEPA",
          "TOSS",
          "UPI",
          "WECHAT_PAY",
          "WIRE",
          "QUOTE",
          "PURCHASE_ORDER",
          "UNKNOWN"
        ],
        "example": "CARD"
      },
      "CustomerRequest": {
        "type": "object",
        "description": "Specifies the customer and billing information applied to the order session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account. Requires an authenticated request.",
            "example": "ABC1234567890",
            "maxLength": 64
          },
          "externalAccountId": {
            "type": "string",
            "description": "An external ID used to link the buyer to your internal systems. Requires an authenticated request.",
            "example": "EXT-98765",
            "minLength": 4,
            "maxLength": 512,
            "format": "regex",
            "pattern": "^[_a-zA-Z0-9-]+$"
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContact"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddress"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContact"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddress"
          },
          "shipToType": {
            "type": "string",
            "description": "Identifies the type of shipping destination mapping applied to the order.",
            "example": "SAME_AS_BILL_TO",
            "enum": [
              "GIFT_PURCHASE",
              "SHIP_TO",
              "SAME_AS_BILL_TO"
            ]
          },
          "accountTags": {
            "type": "object",
            "additionalProperties": {
              "type": "string"
            },
            "description": "A key-value map of custom tags applied to the buyer's account. Requires an authenticated request.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "Capture the buyer's VAT, GST, or CPF identification number used for tax calculation or exemption. Required for buyers located in Brazil.",
            "example": "DE123456789",
            "maxLength": 255
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA",
            "maxLength": 2
          }
        }
      },
      "BillToContact": {
        "type": "object",
        "description": "Capture the billing contact details used to process the payment, calculate localized taxes, and generate the invoice. Pass standard, single-buyer information here, as this acts as the primary contact for the order.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContact": {
        "type": "object",
        "description": "Capture the recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddress": {
        "type": "object",
        "description": "Capture the physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddress": {
        "type": "object",
        "description": "Capture the physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CartRequest": {
        "type": "object",
        "description": "The collection of line items and applied promotions constituting the order session.",
        "properties": {
          "couponCode": {
            "type": "string",
            "description": "The promotional coupon code applied to the cart totals.",
            "example": "SUMMER_2026",
            "maxLength": 256
          },
          "lineItems": {
            "type": "array",
            "description": "A list of products populated in the session cart.",
            "items": {
              "$ref": "#/components/schemas/OrderItemRequest"
            }
          }
        }
      },
      "OrderItemRequest": {
        "type": "object",
        "description": "Details a specific line item being added or modified within a cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the catalog product to add.",
            "example": "gold-tier",
            "maxLength": 256
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units to purchase. Defaults to 1 if omitted.",
            "example": 1
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout. Requires an authenticated request.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The default quantity presented at checkout. Requires an authenticated request.",
            "example": 1
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "customPrice": {
            "type": "object",
            "description": "Overrides the base catalog price with a custom defined pricing structure. Requires an authenticated request.",
            "properties": {
              "unitPrice": {
                "description": "The custom flat price mapping applied to a single unit.",
                "$ref": "#/components/schemas/PriceMap"
              },
              "discounts": {
                "type": "array",
                "description": "A list of volume-based discounting tiers. Applicable ranges must not overlap.",
                "items": {
                  "type": "object",
                  "properties": {
                    "minQuantity": {
                      "type": "integer",
                      "description": "The minimum volume of units required to trigger this discount tier. Defaults to `1`.",
                      "example": 2
                    },
                    "amountDiscount": {
                      "$ref": "#/components/schemas/PriceMap",
                      "description": "A fixed flat amount deducted per unit within this tier."
                    },
                    "percentDiscount": {
                      "type": "integer",
                      "description": "A percentage amount deducted per unit within this tier.",
                      "example": 10
                    }
                  }
                }
              },
              "discountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods this discount persists (only applicable to subscription plans; does not apply to one-time products).",
                "example": 3
              },
              "setupFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed setup fee applied exclusively to subscription products."
              },
              "edsFee": {
                "description": "Define the price the buyer pays for the Extended Download Service (EDS), which extends digital file access from 7 days to 1 year.\n\nThis fee applies once per order and must exceed the $1.50 base fee charged by FastSpring.\n",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/PriceMap"
                  }
                ]
              },
              "shippingFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed shipping fee applied to physical goods."
              }
            }
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata tied specifically to this order item. Requires an authenticated request.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscriptionOverrides": {
            "type": "object",
            "description": "Custom subscription configurations that override the base catalog setup for this specific line item. Requires an authenticated request.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        },
        "required": [
          "productPath"
        ]
      },
      "LanguageMap": {
        "type": "object",
        "description": "A map of localized strings based on ISO 639-1 language codes.",
        "properties": {
          "ar": {
            "type": "string",
            "example": "Arabic",
            "maxLength": 512
          },
          "cs": {
            "type": "string",
            "example": "Czech",
            "maxLength": 512
          },
          "da": {
            "type": "string",
            "example": "Danish",
            "maxLength": 512
          },
          "de": {
            "type": "string",
            "example": "German",
            "maxLength": 512
          },
          "es": {
            "type": "string",
            "example": "Spanish",
            "maxLength": 512
          },
          "en": {
            "type": "string",
            "example": "English",
            "maxLength": 512
          },
          "fi": {
            "type": "string",
            "example": "Finnish",
            "maxLength": 512
          },
          "fr": {
            "type": "string",
            "example": "French",
            "maxLength": 512
          },
          "hr": {
            "type": "string",
            "example": "Croatian",
            "maxLength": 512
          },
          "it": {
            "type": "string",
            "example": "Italian",
            "maxLength": 512
          },
          "iw": {
            "type": "string",
            "example": "Hebrew",
            "maxLength": 512
          },
          "ja": {
            "type": "string",
            "example": "Japanese",
            "maxLength": 512
          },
          "ko": {
            "type": "string",
            "example": "Korean",
            "maxLength": 512
          },
          "nl": {
            "type": "string",
            "example": "Dutch",
            "maxLength": 512
          },
          "no": {
            "type": "string",
            "example": "Norwegian",
            "maxLength": 512
          },
          "pl": {
            "type": "string",
            "example": "Polish",
            "maxLength": 512
          },
          "pt": {
            "type": "string",
            "example": "Portuguese",
            "maxLength": 512
          },
          "ru": {
            "type": "string",
            "example": "Russian",
            "maxLength": 512
          },
          "sk": {
            "type": "string",
            "example": "Slovak",
            "maxLength": 512
          },
          "sv": {
            "type": "string",
            "example": "Swedish",
            "maxLength": 512
          },
          "tr": {
            "type": "string",
            "example": "Turkish",
            "maxLength": 512
          },
          "zh": {
            "type": "string",
            "example": "Chinese",
            "maxLength": 512
          }
        }
      },
      "ProductDescription": {
        "type": "object",
        "description": "Specifies localized descriptive fields for a product. Requires an authenticated request to modify.",
        "properties": {
          "display": {
            "description": "The primary title of the product presented to the buyer.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "instructions": {
            "description": "The post-purchase instructions displayed for the product.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "summary": {
            "description": "A summary description appearing under the main product title.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "imageUrl": {
            "type": "string",
            "description": "The absolute URL of the product's primary image.",
            "example": "https://cdn.onfastspring.com/themes/images/default-store-logo.png"
          }
        }
      },
      "PriceMap": {
        "type": "object",
        "description": "A map of fixed prices across supported currencies.",
        "properties": {
          "AED": {
            "type": "number",
            "example": 10
          },
          "ARS": {
            "type": "number",
            "example": 10
          },
          "AUD": {
            "type": "number",
            "example": 10
          },
          "BRL": {
            "type": "number",
            "example": 10
          },
          "CAD": {
            "type": "number",
            "example": 10
          },
          "CHF": {
            "type": "number",
            "example": 10
          },
          "CLP": {
            "type": "number",
            "example": 10
          },
          "CNY": {
            "type": "number",
            "example": 10
          },
          "COP": {
            "type": "number",
            "example": 10
          },
          "CZK": {
            "type": "number",
            "example": 10
          },
          "DKK": {
            "type": "number",
            "example": 10
          },
          "EUR": {
            "type": "number",
            "example": 10
          },
          "GBP": {
            "type": "number",
            "example": 10
          },
          "HKD": {
            "type": "number",
            "example": 10
          },
          "HUF": {
            "type": "number",
            "example": 10
          },
          "IDR": {
            "type": "number",
            "example": 10
          },
          "INR": {
            "type": "number",
            "example": 10
          },
          "JPY": {
            "type": "number",
            "example": 10
          },
          "KRW": {
            "type": "number",
            "example": 10
          },
          "MXN": {
            "type": "number",
            "example": 10
          },
          "MYR": {
            "type": "number",
            "example": 10
          },
          "NOK": {
            "type": "number",
            "example": 10
          },
          "NZD": {
            "type": "number",
            "example": 10
          },
          "PEN": {
            "type": "number",
            "example": 10
          },
          "PHP": {
            "type": "number",
            "example": 10
          },
          "PLN": {
            "type": "number",
            "example": 10
          },
          "RUB": {
            "type": "number",
            "example": 10
          },
          "SAR": {
            "type": "number",
            "example": 10
          },
          "SEK": {
            "type": "number",
            "example": 10
          },
          "SGD": {
            "type": "number",
            "example": 10
          },
          "THB": {
            "type": "number",
            "example": 10
          },
          "TRY": {
            "type": "number",
            "example": 10
          },
          "TWD": {
            "type": "number",
            "example": 10
          },
          "USD": {
            "type": "number",
            "example": 10
          },
          "VND": {
            "type": "number",
            "example": 10
          },
          "ZAR": {
            "type": "number",
            "example": 10
          }
        }
      },
      "IntervalUnit": {
        "type": "string",
        "description": "The unit of time defining a billing or reminder interval. Evaluated in conjunction with an interval length.",
        "enum": [
          "DAY",
          "WEEK",
          "MONTH",
          "YEAR",
          "ON_DEMAND"
        ],
        "example": "MONTH"
      },
      "Interval": {
        "type": "object",
        "description": "Defines the frequency of a recurring billing cycle or scheduled notification.",
        "properties": {
          "intervalUnit": {
            "$ref": "#/components/schemas/IntervalUnit"
          },
          "intervalLength": {
            "type": "integer",
            "description": "The number of units defining the interval.",
            "example": 1
          },
          "intervalCount": {
            "type": "integer",
            "description": "The total number of consecutive intervals that make up this plan or sequence.",
            "example": 1
          }
        }
      },
      "SubscriptionAttribute": {
        "type": "object",
        "description": "Defines configuration attributes specific to subscription products. Requires an authenticated request to modify.",
        "properties": {
          "billingFrequency": {
            "description": "The recurring interval at which the subscription bills.",
            "$ref": "#/components/schemas/Interval"
          },
          "trialDays": {
            "type": "integer",
            "description": "The number of free trial days before the first billing cycle occurs.",
            "example": 14
          }
        }
      },
      "BaseSessionResponse": {
        "type": "object",
        "description": "Defines the core architecture and calculated state of a returned order session.",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique primary identifier representing the established session.",
            "example": "OS123456789012345ABC"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking the exact moment the session was initialized.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking when the session data expires and requires recreation.",
            "example": "2026-03-21T10:49:51.000Z"
          },
          "status": {
            "type": "string",
            "description": "Describes the current lifecycle phase of the session.",
            "example": "OPEN",
            "enum": [
              "OPEN",
              "EXPIRED",
              "CANCELLED",
              "PENDING_ORDER",
              "COMPLETED",
              "FAILED"
            ]
          },
          "locale": {
            "type": "string",
            "description": "The display language code currently applied to the session formatting.",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "The 2-letter ISO country code denoting the buyer's billing location utilized for localized pricing.",
            "example": "US"
          },
          "currency": {
            "type": "string",
            "description": "The active currency code utilized for all pricing calculations.",
            "example": "USD"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates `true` if the session is currently transacting real funds against live merchant accounts.",
            "example": true
          },
          "customer": {
            "$ref": "#/components/schemas/CustomerResponse"
          },
          "orderTags": {
            "type": "object",
            "description": "A key-value map of custom metadata configured to attach to the finalized order record.",
            "example": {
              "campaign": "Q1_Social"
            }
          },
          "cart": {
            "$ref": "#/components/schemas/CartResponse"
          },
          "checkoutUrls": {
            "type": "object",
            "description": "A collection mapping distinct frontend URL flows required to render the session.",
            "properties": {
              "webcheckoutUrl": {
                "type": "string",
                "description": "The absolute URL designed to immediately redirect the buyer to a hosted Web checkout.",
                "example": "https://example.onfastspring.com/checkout/session/OS123456789012345ABC"
              }
            }
          },
          "warnings": {
            "type": "array",
            "description": "A list detailing any non-fatal errors or ignored input values encountered while actively processing the session.",
            "items": {
              "$ref": "#/components/schemas/Warning"
            }
          },
          "checkoutStatus": {
            "type": "array",
            "description": "Indicates the current rendering state of the checkout session. \n* `READY_FOR_CHECKOUT`: The session is fully populated and capable of processing.\n* `PRODUCTS_REQUIRED`: The checkout cannot render because no products have been added to the session.\n* `CONCLUDED`: The checkout session has been finalized.\n",
            "example": [
              "PRODUCTS_REQUIRED"
            ],
            "items": {
              "type": "string",
              "enum": [
                "PRODUCTS_REQUIRED",
                "READY_FOR_CHECKOUT",
                "CONCLUDED"
              ]
            }
          }
        }
      },
      "BillToContactResponse": {
        "type": "object",
        "description": "The billing contact details used to process the payment, calculate localized taxes, and generate the invoice.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContactResponse": {
        "type": "object",
        "description": "The recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddressResponse": {
        "type": "object",
        "description": "The physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddressResponse": {
        "type": "object",
        "description": "The physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CustomerResponse": {
        "type": "object",
        "description": "Consolidates the established customer and billing data associated with the session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account record. Revealed only on authenticated requests.",
            "example": "ABC1234567890"
          },
          "externalAccountId": {
            "type": "string",
            "description": "The external ID mapping the buyer to a seller's internal platform. Revealed only on authenticated requests.",
            "example": "EXT-98765"
          },
          "savedPaymentMethod": {
            "type": "object",
            "description": "The details of a previously vaulted payment method associated with the buyer's account.",
            "properties": {
              "paymentMethodType": {
                "type": "string",
                "description": "Identifies the specific payment method brand or channel.",
                "example": "CARD"
              },
              "display": {
                "type": "string",
                "description": "A masked identifier safely describing the vaulted payment method, generally exposing only the last 4 digits.",
                "example": "VISA - 4242"
              }
            }
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContactResponse"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddressResponse"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContactResponse"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddressResponse"
          },
          "accountTags": {
            "type": "object",
            "description": "A key-value map of custom tags applied to the buyer's account.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "The buyer's active VAT, GST, or CPF identification number.",
            "example": "DE123456789"
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA"
          }
        }
      },
      "OrderItemResponse": {
        "type": "object",
        "description": "Details the state and calculated pricing for a specific line item within the active cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the selected product.",
            "example": "gold-tier"
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units configured for purchase.",
            "example": 2
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The original default quantity associated with the item before any modifications.",
            "example": 1
          },
          "virtualProduct": {
            "type": "boolean",
            "description": "Indicates `true` if the item was dynamically constructed in the request and does not map to a saved catalog product.",
            "example": false
          },
          "price": {
            "type": "object",
            "description": "Contains all calculated price elements for the line item including discounts, totals, and localized formatting.",
            "properties": {
              "unitNetPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 90
              },
              "unitNetPriceDisplay": {
                "type": "string",
                "description": "The `unitNetPrice` mapped to a localized currency string.",
                "example": "$90.00"
              },
              "unitListPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, ignoring any applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 100
              },
              "unitListPriceDisplay": {
                "type": "string",
                "description": "The `unitListPrice` mapped to a localized currency string.",
                "example": "$100.00"
              },
              "unitDiscount": {
                "type": "number",
                "description": "The absolute discount value deducted from 1 unit.",
                "example": 10
              },
              "unitDiscountDisplay": {
                "type": "string",
                "description": "The `unitDiscount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTotalDiscount": {
                "type": "number",
                "description": "The absolute total discount value calculated across all units (`unitDiscount` * `quantity`).",
                "example": 20
              },
              "extendedTotalDiscountDisplay": {
                "type": "string",
                "description": "The `extendedTotalDiscount` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "extendedNetTotal": {
                "type": "number",
                "description": "The final, grand total calculated for all units, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 180
              },
              "extendedNetTotalDisplay": {
                "type": "string",
                "description": "The `extendedNetTotal` mapped to a localized currency string.",
                "example": "$180.00"
              },
              "extendedListTotal": {
                "type": "number",
                "description": "The subtotal calculated for all units, ignoring applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 200
              },
              "extendedListTotalDisplay": {
                "type": "string",
                "description": "The `extendedListTotal` mapped to a localized currency string.",
                "example": "$200.00"
              },
              "taxIncluded": {
                "type": "string",
                "description": "Indicates the tax calculation mode applied to the returned price fields.",
                "enum": [
                  "TAXES_INCLUDED_IN_PRICE",
                  "TAXES_ADDED_TO_PRICE"
                ],
                "example": "TAXES_INCLUDED_IN_PRICE"
              },
              "unitTaxAmount": {
                "type": "number",
                "description": "The absolute tax amount calculated for exactly 1 unit.",
                "example": 10
              },
              "unitTaxAmountDisplay": {
                "type": "string",
                "description": "The `unitTaxAmount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTaxTotal": {
                "type": "number",
                "description": "The absolute total tax amount calculated across all units (`unitTaxAmount` * `quantity`).",
                "example": 20
              },
              "extendedTaxTotalDisplay": {
                "type": "string",
                "description": "The `extendedTaxTotal` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "taxPercent": {
                "type": "number",
                "description": "The effective tax rate percentage calculated for the item based on the buyer's location and tax status.",
                "example": 10
              },
              "taxExempt": {
                "type": "boolean",
                "description": "Indicates `true` if the buyer qualifies for zero-rated taxes based on an evaluated `taxId`.",
                "example": false
              },
              "productDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a product-level discount will be applied.",
                "example": 100
              },
              "couponDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a coupon-level discount will be applied.",
                "example": 100
              }
            }
          },
          "productType": {
            "type": "string",
            "description": "Categorizes the product as either a one-time purchase or a recurring charge.",
            "example": "ONE_TIME",
            "enum": [
              "ONE_TIME",
              "SUBSCRIPTION_PLAN"
            ]
          },
          "removable": {
            "type": "boolean",
            "description": "Indicates `true` if the interface should allow the buyer to remove the item from the cart.",
            "example": true
          },
          "bundle": {
            "type": "boolean",
            "description": "Indicates `true` if the item serves as a parent bundle containing sub-products.",
            "example": false
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "productFormat": {
            "type": "string",
            "description": "Categorizes the fulfillment logic format of the product.",
            "example": "DIGITAL",
            "enum": [
              "DIGITAL",
              "PHYSICAL"
            ]
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata associated with the line item.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscription": {
            "type": "object",
            "description": "The active subscription configurations appended to the item, including defined schedules and notification intervals.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        }
      },
      "CartResponse": {
        "type": "object",
        "description": "Details the active state of the cart, providing a list of line items and the finalized calculated totals for the entire session.",
        "properties": {
          "couponCode": {
            "type": "string",
            "description": "The specific promotional coupon code evaluated by the session.",
            "example": "10_OFF"
          },
          "couponHasApplied": {
            "type": "boolean",
            "description": "Indicates `true` if the submitted `couponCode` successfully validated against the cart's line items and triggered a discount.",
            "example": true
          },
          "lineItems": {
            "type": "array",
            "description": "The list of calculated product items currently residing in the session.",
            "items": {
              "$ref": "#/components/schemas/OrderItemResponse"
            }
          },
          "netTotal": {
            "type": "number",
            "description": "The grand total calculated for the cart, factoring in all applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 90
          },
          "netTotalDisplay": {
            "type": "string",
            "description": "The `netTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "listTotal": {
            "type": "number",
            "description": "The subtotal calculated for the cart, ignoring applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 100
          },
          "listTotalDisplay": {
            "type": "string",
            "description": "The `listTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in both applied discounts and evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 90
          },
          "withTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withTaxNetTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "withTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts but factoring in evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 100
          },
          "withTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withTaxListTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withoutTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in applied discounts but stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 70
          },
          "withoutTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxNetTotal` mapped to a localized currency string.",
            "example": "$70.00"
          },
          "withoutTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts and stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 80
          },
          "withoutTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxListTotal` mapped to a localized currency string.",
            "example": "$80.00"
          },
          "discountTotal": {
            "type": "number",
            "description": "The absolute sum of all discounts (both product and coupon) deducted from the cart.",
            "example": 10
          },
          "discountTotalDisplay": {
            "type": "string",
            "description": "The `discountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "couponDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by an evaluated coupon.",
            "example": 10
          },
          "couponDiscountTotalDisplay": {
            "type": "string",
            "description": "The `couponDiscountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "productDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by configured volume or catalog product discounts.",
            "example": 0
          },
          "productDiscountTotalDisplay": {
            "type": "string",
            "description": "The `productDiscountTotal` mapped to a localized currency string.",
            "example": "$0.00"
          },
          "taxIncluded": {
            "type": "string",
            "description": "Indicates the tax calculation mode applied to the returned core price fields.",
            "example": "TAXES_INCLUDED_IN_PRICE",
            "enum": [
              "TAXES_INCLUDED_IN_PRICE",
              "TAXES_ADDED_TO_PRICE"
            ]
          },
          "taxTotal": {
            "type": "number",
            "description": "The absolute sum of all taxes calculated across the cart.",
            "example": 20
          },
          "taxTotalDisplay": {
            "type": "string",
            "description": "The `taxTotal` mapped to a localized currency string.",
            "example": "$20.00"
          },
          "taxRate": {
            "type": "number",
            "description": "The effective tax rate percentage calculated across the cart based on the buyer's location.",
            "example": 28.57
          }
        }
      },
      "Warning": {
        "type": "object",
        "description": "Details non-fatal errors that occurred during session execution. Warnings permit the session to process successfully but flag ignored inputs or invalid configurations.",
        "properties": {
          "code": {
            "type": "string",
            "description": "A constant code mapping to the specific type of warning triggered. Used to drive conditional messaging in the frontend.",
            "example": "INVALID_PROMO_CODE",
            "enum": [
              "INVALID_PROMO_CODE",
              "INVALID_COUNTRY",
              "CHECKOUT_NOT_LIVE",
              "INVALID_TAX_ID",
              "INVALID_BUYER_IP",
              "INVALID_LOCALE",
              "INVALID_POSTAL_CODE"
            ]
          },
          "field": {
            "type": "string",
            "description": "The name of the specific input parameter or field that triggered the warning.",
            "example": "promoCode"
          },
          "message": {
            "type": "string",
            "description": "A human-readable description detailing the cause of the warning.",
            "example": "The promo code you entered is invalid and was not applied. Please check the code."
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    },
    "examples": {
      "AllFields": {
        "summary": "Request with all fields",
        "value": {
          "locale": "en",
          "country": "US",
          "buyerIp": "127.0.0.1",
          "live": true,
          "customer": {
            "accountId": "ABC123",
            "externalAccountId": "EDF456",
            "billToContact": {
              "email": "support@fastspring.com",
              "firstName": "John",
              "lastName": "Doe",
              "company": "Acme Inc",
              "phoneNumber": "555-555-5555"
            },
            "billToAddress": {
              "addressLine1": "123 Main St",
              "addressLine2": "Apt 4",
              "city": "Anytown",
              "postalCode": "93101",
              "country": "US"
            },
            "shipToContact": {
              "email": "support@fastspring.com",
              "firstName": "John",
              "lastName": "Doe",
              "phoneNumber": "555-555-5555"
            },
            "accountTags": {
              "customerType": "VIP",
              "segment": "enterprise"
            },
            "taxId": "123456789",
            "taxIdRegion": "CA"
          },
          "orderTags": {
            "gamerId": "123456"
          },
          "cart": {
            "couponCode": "SUMMER2026",
            "lineItems": [
              {
                "productPath": "gold-tier",
                "quantity": 1,
                "quantityDefault": true,
                "descriptions": {
                  "display": {
                    "en": "Title of Product"
                  },
                  "instructions": {
                    "en": "Post buy instructions"
                  },
                  "summary": {
                    "en": "Summary description"
                  }
                },
                "imageUrl": "https://cdn.onfastspring.com/themes/images/default-store-logo.png",
                "customPrice": {
                  "unitPrice": {
                    "USD": 9.99,
                    "GBP": 7.99
                  },
                  "discounts": [
                    {
                      "minQuantity": 2,
                      "amountDiscount": {
                        "USD": 1,
                        "GBP": 0.8
                      }
                    }
                  ]
                }
              }
            ]
          },
          "paymentMethodsOrder": [
            "card",
            "paypal"
          ],
          "hidePaymentMethods": [
            "wire"
          ]
        }
      },
      "MinFields": {
        "summary": "Request with minimum fields",
        "value": {
          "locale": "de",
          "buyerIp": "127.0.0.1",
          "customer": {
            "billToContact": {
              "email": "support@fastspring.com",
              "firstName": "John",
              "lastName": "Doe"
            },
            "billToAddress": {
              "country": "DE"
            }
          },
          "cart": {
            "lineItems": [
              {
                "productPath": "bronze-tier",
                "quantity": 1
              }
            ]
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Retrieve session

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve session

Retrieves the complete details and current state of an existing order session.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}": {
      "get": {
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          }
        ],
        "tags": [
          "Session"
        ],
        "summary": "Retrieve session",
        "description": "Retrieves the complete details and current state of an existing order session.",
        "operationId": "retrieveSession",
        "responses": {
          "200": {
            "description": "Successfully retrieved the session details.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SessionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates an invalid checkout path or session format.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
                }
              }
            }
          },
          "404": {
            "description": "Not found. The specified session ID does not exist.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "PaymentMethodType": {
        "type": "string",
        "description": "Identifies the specific payment method brand or channel.",
        "enum": [
          "ACH",
          "ALIPAY",
          "AMAZON",
          "APPLE_PAY",
          "CARD",
          "GOOGLE_PAY",
          "IDEAL",
          "KAKAOPAY",
          "KLARNA",
          "MERCADO_PAGO",
          "PAYPAL",
          "PIX",
          "SEPA",
          "TOSS",
          "UPI",
          "WECHAT_PAY",
          "WIRE",
          "QUOTE",
          "PURCHASE_ORDER",
          "UNKNOWN"
        ],
        "example": "CARD"
      },
      "FieldElement": {
        "type": "array",
        "description": "A list of specific buyer data points associated with a checkout interface form.",
        "items": {
          "type": "string",
          "enum": [
            "POSTAL_CODE",
            "NAME",
            "EMAIL",
            "PHONE",
            "COMPANY",
            "CPF",
            "BILLING_ADDRESS",
            "SHIPPING_ADDRESS",
            "ACH",
            "SEPA",
            "CARD"
          ]
        }
      },
      "LanguageMap": {
        "type": "object",
        "description": "A map of localized strings based on ISO 639-1 language codes.",
        "properties": {
          "ar": {
            "type": "string",
            "example": "Arabic",
            "maxLength": 512
          },
          "cs": {
            "type": "string",
            "example": "Czech",
            "maxLength": 512
          },
          "da": {
            "type": "string",
            "example": "Danish",
            "maxLength": 512
          },
          "de": {
            "type": "string",
            "example": "German",
            "maxLength": 512
          },
          "es": {
            "type": "string",
            "example": "Spanish",
            "maxLength": 512
          },
          "en": {
            "type": "string",
            "example": "English",
            "maxLength": 512
          },
          "fi": {
            "type": "string",
            "example": "Finnish",
            "maxLength": 512
          },
          "fr": {
            "type": "string",
            "example": "French",
            "maxLength": 512
          },
          "hr": {
            "type": "string",
            "example": "Croatian",
            "maxLength": 512
          },
          "it": {
            "type": "string",
            "example": "Italian",
            "maxLength": 512
          },
          "iw": {
            "type": "string",
            "example": "Hebrew",
            "maxLength": 512
          },
          "ja": {
            "type": "string",
            "example": "Japanese",
            "maxLength": 512
          },
          "ko": {
            "type": "string",
            "example": "Korean",
            "maxLength": 512
          },
          "nl": {
            "type": "string",
            "example": "Dutch",
            "maxLength": 512
          },
          "no": {
            "type": "string",
            "example": "Norwegian",
            "maxLength": 512
          },
          "pl": {
            "type": "string",
            "example": "Polish",
            "maxLength": 512
          },
          "pt": {
            "type": "string",
            "example": "Portuguese",
            "maxLength": 512
          },
          "ru": {
            "type": "string",
            "example": "Russian",
            "maxLength": 512
          },
          "sk": {
            "type": "string",
            "example": "Slovak",
            "maxLength": 512
          },
          "sv": {
            "type": "string",
            "example": "Swedish",
            "maxLength": 512
          },
          "tr": {
            "type": "string",
            "example": "Turkish",
            "maxLength": 512
          },
          "zh": {
            "type": "string",
            "example": "Chinese",
            "maxLength": 512
          }
        }
      },
      "ProductDescription": {
        "type": "object",
        "description": "Specifies localized descriptive fields for a product. Requires an authenticated request to modify.",
        "properties": {
          "display": {
            "description": "The primary title of the product presented to the buyer.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "instructions": {
            "description": "The post-purchase instructions displayed for the product.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "summary": {
            "description": "A summary description appearing under the main product title.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "imageUrl": {
            "type": "string",
            "description": "The absolute URL of the product's primary image.",
            "example": "https://cdn.onfastspring.com/themes/images/default-store-logo.png"
          }
        }
      },
      "IntervalUnit": {
        "type": "string",
        "description": "The unit of time defining a billing or reminder interval. Evaluated in conjunction with an interval length.",
        "enum": [
          "DAY",
          "WEEK",
          "MONTH",
          "YEAR",
          "ON_DEMAND"
        ],
        "example": "MONTH"
      },
      "Interval": {
        "type": "object",
        "description": "Defines the frequency of a recurring billing cycle or scheduled notification.",
        "properties": {
          "intervalUnit": {
            "$ref": "#/components/schemas/IntervalUnit"
          },
          "intervalLength": {
            "type": "integer",
            "description": "The number of units defining the interval.",
            "example": 1
          },
          "intervalCount": {
            "type": "integer",
            "description": "The total number of consecutive intervals that make up this plan or sequence.",
            "example": 1
          }
        }
      },
      "SubscriptionAttribute": {
        "type": "object",
        "description": "Defines configuration attributes specific to subscription products. Requires an authenticated request to modify.",
        "properties": {
          "billingFrequency": {
            "description": "The recurring interval at which the subscription bills.",
            "$ref": "#/components/schemas/Interval"
          },
          "trialDays": {
            "type": "integer",
            "description": "The number of free trial days before the first billing cycle occurs.",
            "example": 14
          }
        }
      },
      "SessionResponse": {
        "type": "object",
        "description": "Defines the core architecture and calculated state of a returned order session.",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique primary identifier representing the established session.",
            "example": "OS123456789012345ABC"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking the exact moment the session was initialized.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking when the session data expires and requires recreation.",
            "example": "2026-03-21T10:49:51.000Z"
          },
          "status": {
            "type": "string",
            "description": "Describes the current lifecycle phase of the session.",
            "example": "OPEN",
            "enum": [
              "OPEN",
              "EXPIRED",
              "CANCELLED",
              "PENDING_ORDER",
              "COMPLETED",
              "FAILED"
            ]
          },
          "locale": {
            "type": "string",
            "description": "The display language code currently applied to the session formatting.",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "The 2-letter ISO country code denoting the buyer's billing location utilized for localized pricing.",
            "example": "US"
          },
          "currency": {
            "type": "string",
            "description": "The active currency code utilized for all pricing calculations.",
            "example": "USD"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates `true` if the session is currently transacting real funds against live merchant accounts.",
            "example": true
          },
          "customer": {
            "$ref": "#/components/schemas/CustomerResponse"
          },
          "orderTags": {
            "type": "object",
            "description": "A key-value map of custom metadata configured to attach to the finalized order record.",
            "example": {
              "campaign": "Q1_Social"
            }
          },
          "cart": {
            "$ref": "#/components/schemas/CartResponse"
          },
          "paymentMethods": {
            "type": "array",
            "description": "A structured, ordered list specifying the active payment methods the session makes available to the buyer.",
            "items": {
              "$ref": "#/components/schemas/PaymentMethod"
            }
          },
          "hidePaymentMethods": {
            "type": "array",
            "description": "A list identifying specific payment methods explicitly filtered or hidden from the interface.",
            "example": [
              "WIRE"
            ],
            "items": {
              "type": "string"
            }
          },
          "checkoutUrls": {
            "type": "object",
            "description": "A collection mapping distinct frontend URL flows required to render the session.",
            "properties": {
              "webcheckoutUrl": {
                "type": "string",
                "description": "The absolute URL designed to immediately redirect the buyer to a hosted Web checkout.",
                "example": "https://example.onfastspring.com/checkout/session/OS123456789012345ABC"
              }
            }
          },
          "warnings": {
            "type": "array",
            "description": "A list detailing any non-fatal errors or ignored input values encountered while actively processing the session.",
            "items": {
              "$ref": "#/components/schemas/Warning"
            }
          },
          "checkoutStatus": {
            "type": "array",
            "description": "Indicates the current rendering state of the checkout session. \n* `READY_FOR_CHECKOUT`: The session is fully populated and capable of processing.\n* `PRODUCTS_REQUIRED`: The checkout cannot render because no products have been added to the session.\n* `CONCLUDED`: The checkout session has been finalized.\n",
            "example": [
              "PRODUCTS_REQUIRED"
            ],
            "items": {
              "type": "string",
              "enum": [
                "PRODUCTS_REQUIRED",
                "READY_FOR_CHECKOUT",
                "CONCLUDED"
              ]
            }
          }
        }
      },
      "BillToContactResponse": {
        "type": "object",
        "description": "The billing contact details used to process the payment, calculate localized taxes, and generate the invoice.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContactResponse": {
        "type": "object",
        "description": "The recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddressResponse": {
        "type": "object",
        "description": "The physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddressResponse": {
        "type": "object",
        "description": "The physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CustomerResponse": {
        "type": "object",
        "description": "Consolidates the established customer and billing data associated with the session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account record. Revealed only on authenticated requests.",
            "example": "ABC1234567890"
          },
          "externalAccountId": {
            "type": "string",
            "description": "The external ID mapping the buyer to a seller's internal platform. Revealed only on authenticated requests.",
            "example": "EXT-98765"
          },
          "savedPaymentMethod": {
            "type": "object",
            "description": "The details of a previously vaulted payment method associated with the buyer's account.",
            "properties": {
              "paymentMethodType": {
                "type": "string",
                "description": "Identifies the specific payment method brand or channel.",
                "example": "CARD"
              },
              "display": {
                "type": "string",
                "description": "A masked identifier safely describing the vaulted payment method, generally exposing only the last 4 digits.",
                "example": "VISA - 4242"
              }
            }
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContactResponse"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddressResponse"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContactResponse"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddressResponse"
          },
          "accountTags": {
            "type": "object",
            "description": "A key-value map of custom tags applied to the buyer's account.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "The buyer's active VAT, GST, or CPF identification number.",
            "example": "DE123456789"
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA"
          }
        }
      },
      "OrderItemResponse": {
        "type": "object",
        "description": "Details the state and calculated pricing for a specific line item within the active cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the selected product.",
            "example": "gold-tier"
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units configured for purchase.",
            "example": 2
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The original default quantity associated with the item before any modifications.",
            "example": 1
          },
          "virtualProduct": {
            "type": "boolean",
            "description": "Indicates `true` if the item was dynamically constructed in the request and does not map to a saved catalog product.",
            "example": false
          },
          "price": {
            "type": "object",
            "description": "Contains all calculated price elements for the line item including discounts, totals, and localized formatting.",
            "properties": {
              "unitNetPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 90
              },
              "unitNetPriceDisplay": {
                "type": "string",
                "description": "The `unitNetPrice` mapped to a localized currency string.",
                "example": "$90.00"
              },
              "unitListPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, ignoring any applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 100
              },
              "unitListPriceDisplay": {
                "type": "string",
                "description": "The `unitListPrice` mapped to a localized currency string.",
                "example": "$100.00"
              },
              "unitDiscount": {
                "type": "number",
                "description": "The absolute discount value deducted from 1 unit.",
                "example": 10
              },
              "unitDiscountDisplay": {
                "type": "string",
                "description": "The `unitDiscount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTotalDiscount": {
                "type": "number",
                "description": "The absolute total discount value calculated across all units (`unitDiscount` * `quantity`).",
                "example": 20
              },
              "extendedTotalDiscountDisplay": {
                "type": "string",
                "description": "The `extendedTotalDiscount` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "extendedNetTotal": {
                "type": "number",
                "description": "The final, grand total calculated for all units, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 180
              },
              "extendedNetTotalDisplay": {
                "type": "string",
                "description": "The `extendedNetTotal` mapped to a localized currency string.",
                "example": "$180.00"
              },
              "extendedListTotal": {
                "type": "number",
                "description": "The subtotal calculated for all units, ignoring applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 200
              },
              "extendedListTotalDisplay": {
                "type": "string",
                "description": "The `extendedListTotal` mapped to a localized currency string.",
                "example": "$200.00"
              },
              "taxIncluded": {
                "type": "string",
                "description": "Indicates the tax calculation mode applied to the returned price fields.",
                "enum": [
                  "TAXES_INCLUDED_IN_PRICE",
                  "TAXES_ADDED_TO_PRICE"
                ],
                "example": "TAXES_INCLUDED_IN_PRICE"
              },
              "unitTaxAmount": {
                "type": "number",
                "description": "The absolute tax amount calculated for exactly 1 unit.",
                "example": 10
              },
              "unitTaxAmountDisplay": {
                "type": "string",
                "description": "The `unitTaxAmount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTaxTotal": {
                "type": "number",
                "description": "The absolute total tax amount calculated across all units (`unitTaxAmount` * `quantity`).",
                "example": 20
              },
              "extendedTaxTotalDisplay": {
                "type": "string",
                "description": "The `extendedTaxTotal` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "taxPercent": {
                "type": "number",
                "description": "The effective tax rate percentage calculated for the item based on the buyer's location and tax status.",
                "example": 10
              },
              "taxExempt": {
                "type": "boolean",
                "description": "Indicates `true` if the buyer qualifies for zero-rated taxes based on an evaluated `taxId`.",
                "example": false
              },
              "productDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a product-level discount will be applied.",
                "example": 100
              },
              "couponDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a coupon-level discount will be applied.",
                "example": 100
              }
            }
          },
          "productType": {
            "type": "string",
            "description": "Categorizes the product as either a one-time purchase or a recurring charge.",
            "example": "ONE_TIME",
            "enum": [
              "ONE_TIME",
              "SUBSCRIPTION_PLAN"
            ]
          },
          "removable": {
            "type": "boolean",
            "description": "Indicates `true` if the interface should allow the buyer to remove the item from the cart.",
            "example": true
          },
          "bundle": {
            "type": "boolean",
            "description": "Indicates `true` if the item serves as a parent bundle containing sub-products.",
            "example": false
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "productFormat": {
            "type": "string",
            "description": "Categorizes the fulfillment logic format of the product.",
            "example": "DIGITAL",
            "enum": [
              "DIGITAL",
              "PHYSICAL"
            ]
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata associated with the line item.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscription": {
            "type": "object",
            "description": "The active subscription configurations appended to the item, including defined schedules and notification intervals.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        }
      },
      "CartResponse": {
        "type": "object",
        "description": "Details the active state of the cart, providing a list of line items and the finalized calculated totals for the entire session.",
        "properties": {
          "couponCode": {
            "type": "string",
            "description": "The specific promotional coupon code evaluated by the session.",
            "example": "10_OFF"
          },
          "couponHasApplied": {
            "type": "boolean",
            "description": "Indicates `true` if the submitted `couponCode` successfully validated against the cart's line items and triggered a discount.",
            "example": true
          },
          "lineItems": {
            "type": "array",
            "description": "The list of calculated product items currently residing in the session.",
            "items": {
              "$ref": "#/components/schemas/OrderItemResponse"
            }
          },
          "netTotal": {
            "type": "number",
            "description": "The grand total calculated for the cart, factoring in all applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 90
          },
          "netTotalDisplay": {
            "type": "string",
            "description": "The `netTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "listTotal": {
            "type": "number",
            "description": "The subtotal calculated for the cart, ignoring applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 100
          },
          "listTotalDisplay": {
            "type": "string",
            "description": "The `listTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in both applied discounts and evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 90
          },
          "withTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withTaxNetTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "withTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts but factoring in evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 100
          },
          "withTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withTaxListTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withoutTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in applied discounts but stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 70
          },
          "withoutTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxNetTotal` mapped to a localized currency string.",
            "example": "$70.00"
          },
          "withoutTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts and stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 80
          },
          "withoutTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxListTotal` mapped to a localized currency string.",
            "example": "$80.00"
          },
          "discountTotal": {
            "type": "number",
            "description": "The absolute sum of all discounts (both product and coupon) deducted from the cart.",
            "example": 10
          },
          "discountTotalDisplay": {
            "type": "string",
            "description": "The `discountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "couponDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by an evaluated coupon.",
            "example": 10
          },
          "couponDiscountTotalDisplay": {
            "type": "string",
            "description": "The `couponDiscountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "productDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by configured volume or catalog product discounts.",
            "example": 0
          },
          "productDiscountTotalDisplay": {
            "type": "string",
            "description": "The `productDiscountTotal` mapped to a localized currency string.",
            "example": "$0.00"
          },
          "taxIncluded": {
            "type": "string",
            "description": "Indicates the tax calculation mode applied to the returned core price fields.",
            "example": "TAXES_INCLUDED_IN_PRICE",
            "enum": [
              "TAXES_INCLUDED_IN_PRICE",
              "TAXES_ADDED_TO_PRICE"
            ]
          },
          "taxTotal": {
            "type": "number",
            "description": "The absolute sum of all taxes calculated across the cart.",
            "example": 20
          },
          "taxTotalDisplay": {
            "type": "string",
            "description": "The `taxTotal` mapped to a localized currency string.",
            "example": "$20.00"
          },
          "taxRate": {
            "type": "number",
            "description": "The effective tax rate percentage calculated across the cart based on the buyer's location.",
            "example": 28.57
          }
        }
      },
      "PaymentMethod": {
        "type": "object",
        "description": "Maps the supported configurations and requirements for a valid payment method channel.",
        "properties": {
          "id": {
            "$ref": "#/components/schemas/PaymentMethodType"
          },
          "description": {
            "type": "string",
            "description": "The internal system key or localization reference used to map the payment method to its translated display name in the frontend interface.",
            "example": "PaymentMethodType.CreditCard",
            "maxLength": 255
          },
          "requiredFieldElements": {
            "$ref": "#/components/schemas/FieldElement"
          },
          "showFieldElements": {
            "$ref": "#/components/schemas/FieldElement",
            "description": "Show these fields to the buyer on the form. May not be required."
          },
          "supportedProductTypes": {
            "type": "array",
            "description": "A list defining whether the payment method supports one-time purchases, subscription plans, or both.",
            "example": "ONE_TIME",
            "items": {
              "type": "string",
              "enum": [
                "ONE_TIME",
                "SUBSCRIPTION_PLAN"
              ]
            }
          },
          "variants": {
            "type": "array",
            "description": "A list of explicitly supported card brands or sub-channels handled by this payment method.",
            "example": [
              "VISA",
              "MASTER_CARD",
              "AMEX"
            ],
            "items": {
              "type": "string",
              "enum": [
                "VISA",
                "MASTER_CARD",
                "AMEX",
                "ELO",
                "DISCOVER",
                "DINNERS",
                "UNION_PAY",
                "JCB",
                "HIPERCARD"
              ]
            }
          }
        }
      },
      "Warning": {
        "type": "object",
        "description": "Details non-fatal errors that occurred during session execution. Warnings permit the session to process successfully but flag ignored inputs or invalid configurations.",
        "properties": {
          "code": {
            "type": "string",
            "description": "A constant code mapping to the specific type of warning triggered. Used to drive conditional messaging in the frontend.",
            "example": "INVALID_PROMO_CODE",
            "enum": [
              "INVALID_PROMO_CODE",
              "INVALID_COUNTRY",
              "CHECKOUT_NOT_LIVE",
              "INVALID_TAX_ID",
              "INVALID_BUYER_IP",
              "INVALID_LOCALE",
              "INVALID_POSTAL_CODE"
            ]
          },
          "field": {
            "type": "string",
            "description": "The name of the specific input parameter or field that triggered the warning.",
            "example": "promoCode"
          },
          "message": {
            "type": "string",
            "description": "A human-readable description detailing the cause of the warning.",
            "example": "The promo code you entered is invalid and was not applied. Please check the code."
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Update session

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update session

Updates an existing order session. Overwrites customer details, items, and custom prices with the provided arrays. Requires an authenticated request to modify restricted fields.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}": {
      "put": {
        "tags": [
          "Session"
        ],
        "summary": "Update session",
        "description": "Updates an existing order session. Overwrites customer details, items, and custom prices with the provided arrays. Requires an authenticated request to modify restricted fields.",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          }
        ],
        "operationId": "updateSession",
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateSessionRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Successfully updated the session.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SessionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates invalid input properties or values.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "CreateSessionRequest": {
        "type": "object",
        "description": "The primary request payload used to formulate a new order session.",
        "properties": {
          "locale": {
            "type": "string",
            "description": "Set the language for the buyer's experience. Accepts standard 2-letter language codes (e.g., `en`). Defaults to the browser locale if omitted.",
            "example": "en",
            "maxLength": 5
          },
          "country": {
            "type": "string",
            "description": "The 2-letter ISO country code defining the buyer's billing location. Inferred from `buyerIp` if omitted.",
            "example": "US",
            "pattern": "^[A-Za-z]{2}$"
          },
          "buyerIp": {
            "type": "string",
            "description": "The IPv4 or IPv6 address of the buyer. Used to infer `country` and `currency` if they are omitted. When executing an authenticated server-to-server request, this field acts as an override.",
            "example": "127.0.0.1",
            "maxLength": 39
          },
          "live": {
            "type": "boolean",
            "description": "Indicate whether the session processes in live mode (`true`) or test mode (`false`).\nReview the [Test orders](https://developer.fastspring.com/docs/test-orders) documentation to learn how to safely simulate the buyer experience using test credit cards.\n> **Note:** If the targeted checkout is set to 'test mode' in your checkout settings, this value automatically defaults to `false` regardless of what you pass in the request.\n",
            "default": true,
            "example": true
          },
          "customer": {
            "$ref": "#/components/schemas/CustomerRequest"
          },
          "orderTags": {
            "type": "object",
            "description": "A key-value map of custom metadata attached to the finalized order record. Requires an authenticated request.",
            "example": {
              "campaign": "Q1_Social"
            }
          },
          "cart": {
            "$ref": "#/components/schemas/CartRequest"
          },
          "paymentMethodsOrder": {
            "type": "array",
            "description": "Dictates the explicit sorting order of payment methods presented to the buyer. Unsupported payment methods are automatically suppressed. Requires an authenticated request.",
            "example": [
              "CARD",
              "PAYPAL"
            ],
            "items": {
              "$ref": "#/components/schemas/PaymentMethodType"
            }
          },
          "hidePaymentMethods": {
            "type": "array",
            "description": "Explicitly filters the provided payment methods from the buyer's interface. Requires an authenticated request.",
            "example": [
              "WIRE"
            ],
            "items": {
              "$ref": "#/components/schemas/PaymentMethodType"
            }
          }
        },
        "required": [
          "checkoutPath"
        ]
      },
      "PaymentMethodType": {
        "type": "string",
        "description": "Identifies the specific payment method brand or channel.",
        "enum": [
          "ACH",
          "ALIPAY",
          "AMAZON",
          "APPLE_PAY",
          "CARD",
          "GOOGLE_PAY",
          "IDEAL",
          "KAKAOPAY",
          "KLARNA",
          "MERCADO_PAGO",
          "PAYPAL",
          "PIX",
          "SEPA",
          "TOSS",
          "UPI",
          "WECHAT_PAY",
          "WIRE",
          "QUOTE",
          "PURCHASE_ORDER",
          "UNKNOWN"
        ],
        "example": "CARD"
      },
      "FieldElement": {
        "type": "array",
        "description": "A list of specific buyer data points associated with a checkout interface form.",
        "items": {
          "type": "string",
          "enum": [
            "POSTAL_CODE",
            "NAME",
            "EMAIL",
            "PHONE",
            "COMPANY",
            "CPF",
            "BILLING_ADDRESS",
            "SHIPPING_ADDRESS",
            "ACH",
            "SEPA",
            "CARD"
          ]
        }
      },
      "CustomerRequest": {
        "type": "object",
        "description": "Specifies the customer and billing information applied to the order session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account. Requires an authenticated request.",
            "example": "ABC1234567890",
            "maxLength": 64
          },
          "externalAccountId": {
            "type": "string",
            "description": "An external ID used to link the buyer to your internal systems. Requires an authenticated request.",
            "example": "EXT-98765",
            "minLength": 4,
            "maxLength": 512,
            "format": "regex",
            "pattern": "^[_a-zA-Z0-9-]+$"
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContact"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddress"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContact"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddress"
          },
          "shipToType": {
            "type": "string",
            "description": "Identifies the type of shipping destination mapping applied to the order.",
            "example": "SAME_AS_BILL_TO",
            "enum": [
              "GIFT_PURCHASE",
              "SHIP_TO",
              "SAME_AS_BILL_TO"
            ]
          },
          "accountTags": {
            "type": "object",
            "additionalProperties": {
              "type": "string"
            },
            "description": "A key-value map of custom tags applied to the buyer's account. Requires an authenticated request.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "Capture the buyer's VAT, GST, or CPF identification number used for tax calculation or exemption. Required for buyers located in Brazil.",
            "example": "DE123456789",
            "maxLength": 255
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA",
            "maxLength": 2
          }
        }
      },
      "BillToContact": {
        "type": "object",
        "description": "Capture the billing contact details used to process the payment, calculate localized taxes, and generate the invoice. Pass standard, single-buyer information here, as this acts as the primary contact for the order.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContact": {
        "type": "object",
        "description": "Capture the recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddress": {
        "type": "object",
        "description": "Capture the physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddress": {
        "type": "object",
        "description": "Capture the physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CartRequest": {
        "type": "object",
        "description": "The collection of line items and applied promotions constituting the order session.",
        "properties": {
          "couponCode": {
            "type": "string",
            "description": "The promotional coupon code applied to the cart totals.",
            "example": "SUMMER_2026",
            "maxLength": 256
          },
          "lineItems": {
            "type": "array",
            "description": "A list of products populated in the session cart.",
            "items": {
              "$ref": "#/components/schemas/OrderItemRequest"
            }
          }
        }
      },
      "OrderItemRequest": {
        "type": "object",
        "description": "Details a specific line item being added or modified within a cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the catalog product to add.",
            "example": "gold-tier",
            "maxLength": 256
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units to purchase. Defaults to 1 if omitted.",
            "example": 1
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout. Requires an authenticated request.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The default quantity presented at checkout. Requires an authenticated request.",
            "example": 1
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "customPrice": {
            "type": "object",
            "description": "Overrides the base catalog price with a custom defined pricing structure. Requires an authenticated request.",
            "properties": {
              "unitPrice": {
                "description": "The custom flat price mapping applied to a single unit.",
                "$ref": "#/components/schemas/PriceMap"
              },
              "discounts": {
                "type": "array",
                "description": "A list of volume-based discounting tiers. Applicable ranges must not overlap.",
                "items": {
                  "type": "object",
                  "properties": {
                    "minQuantity": {
                      "type": "integer",
                      "description": "The minimum volume of units required to trigger this discount tier. Defaults to `1`.",
                      "example": 2
                    },
                    "amountDiscount": {
                      "$ref": "#/components/schemas/PriceMap",
                      "description": "A fixed flat amount deducted per unit within this tier."
                    },
                    "percentDiscount": {
                      "type": "integer",
                      "description": "A percentage amount deducted per unit within this tier.",
                      "example": 10
                    }
                  }
                }
              },
              "discountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods this discount persists (only applicable to subscription plans; does not apply to one-time products).",
                "example": 3
              },
              "setupFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed setup fee applied exclusively to subscription products."
              },
              "edsFee": {
                "description": "Define the price the buyer pays for the Extended Download Service (EDS), which extends digital file access from 7 days to 1 year.\n\nThis fee applies once per order and must exceed the $1.50 base fee charged by FastSpring.\n",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/PriceMap"
                  }
                ]
              },
              "shippingFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed shipping fee applied to physical goods."
              }
            }
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata tied specifically to this order item. Requires an authenticated request.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscriptionOverrides": {
            "type": "object",
            "description": "Custom subscription configurations that override the base catalog setup for this specific line item. Requires an authenticated request.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        },
        "required": [
          "productPath"
        ]
      },
      "LanguageMap": {
        "type": "object",
        "description": "A map of localized strings based on ISO 639-1 language codes.",
        "properties": {
          "ar": {
            "type": "string",
            "example": "Arabic",
            "maxLength": 512
          },
          "cs": {
            "type": "string",
            "example": "Czech",
            "maxLength": 512
          },
          "da": {
            "type": "string",
            "example": "Danish",
            "maxLength": 512
          },
          "de": {
            "type": "string",
            "example": "German",
            "maxLength": 512
          },
          "es": {
            "type": "string",
            "example": "Spanish",
            "maxLength": 512
          },
          "en": {
            "type": "string",
            "example": "English",
            "maxLength": 512
          },
          "fi": {
            "type": "string",
            "example": "Finnish",
            "maxLength": 512
          },
          "fr": {
            "type": "string",
            "example": "French",
            "maxLength": 512
          },
          "hr": {
            "type": "string",
            "example": "Croatian",
            "maxLength": 512
          },
          "it": {
            "type": "string",
            "example": "Italian",
            "maxLength": 512
          },
          "iw": {
            "type": "string",
            "example": "Hebrew",
            "maxLength": 512
          },
          "ja": {
            "type": "string",
            "example": "Japanese",
            "maxLength": 512
          },
          "ko": {
            "type": "string",
            "example": "Korean",
            "maxLength": 512
          },
          "nl": {
            "type": "string",
            "example": "Dutch",
            "maxLength": 512
          },
          "no": {
            "type": "string",
            "example": "Norwegian",
            "maxLength": 512
          },
          "pl": {
            "type": "string",
            "example": "Polish",
            "maxLength": 512
          },
          "pt": {
            "type": "string",
            "example": "Portuguese",
            "maxLength": 512
          },
          "ru": {
            "type": "string",
            "example": "Russian",
            "maxLength": 512
          },
          "sk": {
            "type": "string",
            "example": "Slovak",
            "maxLength": 512
          },
          "sv": {
            "type": "string",
            "example": "Swedish",
            "maxLength": 512
          },
          "tr": {
            "type": "string",
            "example": "Turkish",
            "maxLength": 512
          },
          "zh": {
            "type": "string",
            "example": "Chinese",
            "maxLength": 512
          }
        }
      },
      "ProductDescription": {
        "type": "object",
        "description": "Specifies localized descriptive fields for a product. Requires an authenticated request to modify.",
        "properties": {
          "display": {
            "description": "The primary title of the product presented to the buyer.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "instructions": {
            "description": "The post-purchase instructions displayed for the product.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "summary": {
            "description": "A summary description appearing under the main product title.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "imageUrl": {
            "type": "string",
            "description": "The absolute URL of the product's primary image.",
            "example": "https://cdn.onfastspring.com/themes/images/default-store-logo.png"
          }
        }
      },
      "PriceMap": {
        "type": "object",
        "description": "A map of fixed prices across supported currencies.",
        "properties": {
          "AED": {
            "type": "number",
            "example": 10
          },
          "ARS": {
            "type": "number",
            "example": 10
          },
          "AUD": {
            "type": "number",
            "example": 10
          },
          "BRL": {
            "type": "number",
            "example": 10
          },
          "CAD": {
            "type": "number",
            "example": 10
          },
          "CHF": {
            "type": "number",
            "example": 10
          },
          "CLP": {
            "type": "number",
            "example": 10
          },
          "CNY": {
            "type": "number",
            "example": 10
          },
          "COP": {
            "type": "number",
            "example": 10
          },
          "CZK": {
            "type": "number",
            "example": 10
          },
          "DKK": {
            "type": "number",
            "example": 10
          },
          "EUR": {
            "type": "number",
            "example": 10
          },
          "GBP": {
            "type": "number",
            "example": 10
          },
          "HKD": {
            "type": "number",
            "example": 10
          },
          "HUF": {
            "type": "number",
            "example": 10
          },
          "IDR": {
            "type": "number",
            "example": 10
          },
          "INR": {
            "type": "number",
            "example": 10
          },
          "JPY": {
            "type": "number",
            "example": 10
          },
          "KRW": {
            "type": "number",
            "example": 10
          },
          "MXN": {
            "type": "number",
            "example": 10
          },
          "MYR": {
            "type": "number",
            "example": 10
          },
          "NOK": {
            "type": "number",
            "example": 10
          },
          "NZD": {
            "type": "number",
            "example": 10
          },
          "PEN": {
            "type": "number",
            "example": 10
          },
          "PHP": {
            "type": "number",
            "example": 10
          },
          "PLN": {
            "type": "number",
            "example": 10
          },
          "RUB": {
            "type": "number",
            "example": 10
          },
          "SAR": {
            "type": "number",
            "example": 10
          },
          "SEK": {
            "type": "number",
            "example": 10
          },
          "SGD": {
            "type": "number",
            "example": 10
          },
          "THB": {
            "type": "number",
            "example": 10
          },
          "TRY": {
            "type": "number",
            "example": 10
          },
          "TWD": {
            "type": "number",
            "example": 10
          },
          "USD": {
            "type": "number",
            "example": 10
          },
          "VND": {
            "type": "number",
            "example": 10
          },
          "ZAR": {
            "type": "number",
            "example": 10
          }
        }
      },
      "IntervalUnit": {
        "type": "string",
        "description": "The unit of time defining a billing or reminder interval. Evaluated in conjunction with an interval length.",
        "enum": [
          "DAY",
          "WEEK",
          "MONTH",
          "YEAR",
          "ON_DEMAND"
        ],
        "example": "MONTH"
      },
      "Interval": {
        "type": "object",
        "description": "Defines the frequency of a recurring billing cycle or scheduled notification.",
        "properties": {
          "intervalUnit": {
            "$ref": "#/components/schemas/IntervalUnit"
          },
          "intervalLength": {
            "type": "integer",
            "description": "The number of units defining the interval.",
            "example": 1
          },
          "intervalCount": {
            "type": "integer",
            "description": "The total number of consecutive intervals that make up this plan or sequence.",
            "example": 1
          }
        }
      },
      "SubscriptionAttribute": {
        "type": "object",
        "description": "Defines configuration attributes specific to subscription products. Requires an authenticated request to modify.",
        "properties": {
          "billingFrequency": {
            "description": "The recurring interval at which the subscription bills.",
            "$ref": "#/components/schemas/Interval"
          },
          "trialDays": {
            "type": "integer",
            "description": "The number of free trial days before the first billing cycle occurs.",
            "example": 14
          }
        }
      },
      "SessionResponse": {
        "type": "object",
        "description": "Defines the core architecture and calculated state of a returned order session.",
        "properties": {
          "id": {
            "type": "string",
            "description": "The unique primary identifier representing the established session.",
            "example": "OS123456789012345ABC"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking the exact moment the session was initialized.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "expires": {
            "type": "string",
            "format": "date-time",
            "description": "The ISO 8601 timestamp marking when the session data expires and requires recreation.",
            "example": "2026-03-21T10:49:51.000Z"
          },
          "status": {
            "type": "string",
            "description": "Describes the current lifecycle phase of the session.",
            "example": "OPEN",
            "enum": [
              "OPEN",
              "EXPIRED",
              "CANCELLED",
              "PENDING_ORDER",
              "COMPLETED",
              "FAILED"
            ]
          },
          "locale": {
            "type": "string",
            "description": "The display language code currently applied to the session formatting.",
            "example": "en"
          },
          "country": {
            "type": "string",
            "description": "The 2-letter ISO country code denoting the buyer's billing location utilized for localized pricing.",
            "example": "US"
          },
          "currency": {
            "type": "string",
            "description": "The active currency code utilized for all pricing calculations.",
            "example": "USD"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates `true` if the session is currently transacting real funds against live merchant accounts.",
            "example": true
          },
          "customer": {
            "$ref": "#/components/schemas/CustomerResponse"
          },
          "orderTags": {
            "type": "object",
            "description": "A key-value map of custom metadata configured to attach to the finalized order record.",
            "example": {
              "campaign": "Q1_Social"
            }
          },
          "cart": {
            "$ref": "#/components/schemas/CartResponse"
          },
          "paymentMethods": {
            "type": "array",
            "description": "A structured, ordered list specifying the active payment methods the session makes available to the buyer.",
            "items": {
              "$ref": "#/components/schemas/PaymentMethod"
            }
          },
          "hidePaymentMethods": {
            "type": "array",
            "description": "A list identifying specific payment methods explicitly filtered or hidden from the interface.",
            "example": [
              "WIRE"
            ],
            "items": {
              "type": "string"
            }
          },
          "checkoutUrls": {
            "type": "object",
            "description": "A collection mapping distinct frontend URL flows required to render the session.",
            "properties": {
              "webcheckoutUrl": {
                "type": "string",
                "description": "The absolute URL designed to immediately redirect the buyer to a hosted Web checkout.",
                "example": "https://example.onfastspring.com/checkout/session/OS123456789012345ABC"
              }
            }
          },
          "warnings": {
            "type": "array",
            "description": "A list detailing any non-fatal errors or ignored input values encountered while actively processing the session.",
            "items": {
              "$ref": "#/components/schemas/Warning"
            }
          },
          "checkoutStatus": {
            "type": "array",
            "description": "Indicates the current rendering state of the checkout session. \n* `READY_FOR_CHECKOUT`: The session is fully populated and capable of processing.\n* `PRODUCTS_REQUIRED`: The checkout cannot render because no products have been added to the session.\n* `CONCLUDED`: The checkout session has been finalized.\n",
            "example": [
              "PRODUCTS_REQUIRED"
            ],
            "items": {
              "type": "string",
              "enum": [
                "PRODUCTS_REQUIRED",
                "READY_FOR_CHECKOUT",
                "CONCLUDED"
              ]
            }
          }
        }
      },
      "BillToContactResponse": {
        "type": "object",
        "description": "The billing contact details used to process the payment, calculate localized taxes, and generate the invoice.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContactResponse": {
        "type": "object",
        "description": "The recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddressResponse": {
        "type": "object",
        "description": "The physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddressResponse": {
        "type": "object",
        "description": "The physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CustomerResponse": {
        "type": "object",
        "description": "Consolidates the established customer and billing data associated with the session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account record. Revealed only on authenticated requests.",
            "example": "ABC1234567890"
          },
          "externalAccountId": {
            "type": "string",
            "description": "The external ID mapping the buyer to a seller's internal platform. Revealed only on authenticated requests.",
            "example": "EXT-98765"
          },
          "savedPaymentMethod": {
            "type": "object",
            "description": "The details of a previously vaulted payment method associated with the buyer's account.",
            "properties": {
              "paymentMethodType": {
                "type": "string",
                "description": "Identifies the specific payment method brand or channel.",
                "example": "CARD"
              },
              "display": {
                "type": "string",
                "description": "A masked identifier safely describing the vaulted payment method, generally exposing only the last 4 digits.",
                "example": "VISA - 4242"
              }
            }
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContactResponse"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddressResponse"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContactResponse"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddressResponse"
          },
          "accountTags": {
            "type": "object",
            "description": "A key-value map of custom tags applied to the buyer's account.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "The buyer's active VAT, GST, or CPF identification number.",
            "example": "DE123456789"
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA"
          }
        }
      },
      "OrderItemResponse": {
        "type": "object",
        "description": "Details the state and calculated pricing for a specific line item within the active cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the selected product.",
            "example": "gold-tier"
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units configured for purchase.",
            "example": 2
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The original default quantity associated with the item before any modifications.",
            "example": 1
          },
          "virtualProduct": {
            "type": "boolean",
            "description": "Indicates `true` if the item was dynamically constructed in the request and does not map to a saved catalog product.",
            "example": false
          },
          "price": {
            "type": "object",
            "description": "Contains all calculated price elements for the line item including discounts, totals, and localized formatting.",
            "properties": {
              "unitNetPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 90
              },
              "unitNetPriceDisplay": {
                "type": "string",
                "description": "The `unitNetPrice` mapped to a localized currency string.",
                "example": "$90.00"
              },
              "unitListPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, ignoring any applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 100
              },
              "unitListPriceDisplay": {
                "type": "string",
                "description": "The `unitListPrice` mapped to a localized currency string.",
                "example": "$100.00"
              },
              "unitDiscount": {
                "type": "number",
                "description": "The absolute discount value deducted from 1 unit.",
                "example": 10
              },
              "unitDiscountDisplay": {
                "type": "string",
                "description": "The `unitDiscount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTotalDiscount": {
                "type": "number",
                "description": "The absolute total discount value calculated across all units (`unitDiscount` * `quantity`).",
                "example": 20
              },
              "extendedTotalDiscountDisplay": {
                "type": "string",
                "description": "The `extendedTotalDiscount` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "extendedNetTotal": {
                "type": "number",
                "description": "The final, grand total calculated for all units, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 180
              },
              "extendedNetTotalDisplay": {
                "type": "string",
                "description": "The `extendedNetTotal` mapped to a localized currency string.",
                "example": "$180.00"
              },
              "extendedListTotal": {
                "type": "number",
                "description": "The subtotal calculated for all units, ignoring applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 200
              },
              "extendedListTotalDisplay": {
                "type": "string",
                "description": "The `extendedListTotal` mapped to a localized currency string.",
                "example": "$200.00"
              },
              "taxIncluded": {
                "type": "string",
                "description": "Indicates the tax calculation mode applied to the returned price fields.",
                "enum": [
                  "TAXES_INCLUDED_IN_PRICE",
                  "TAXES_ADDED_TO_PRICE"
                ],
                "example": "TAXES_INCLUDED_IN_PRICE"
              },
              "unitTaxAmount": {
                "type": "number",
                "description": "The absolute tax amount calculated for exactly 1 unit.",
                "example": 10
              },
              "unitTaxAmountDisplay": {
                "type": "string",
                "description": "The `unitTaxAmount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTaxTotal": {
                "type": "number",
                "description": "The absolute total tax amount calculated across all units (`unitTaxAmount` * `quantity`).",
                "example": 20
              },
              "extendedTaxTotalDisplay": {
                "type": "string",
                "description": "The `extendedTaxTotal` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "taxPercent": {
                "type": "number",
                "description": "The effective tax rate percentage calculated for the item based on the buyer's location and tax status.",
                "example": 10
              },
              "taxExempt": {
                "type": "boolean",
                "description": "Indicates `true` if the buyer qualifies for zero-rated taxes based on an evaluated `taxId`.",
                "example": false
              },
              "productDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a product-level discount will be applied.",
                "example": 100
              },
              "couponDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a coupon-level discount will be applied.",
                "example": 100
              }
            }
          },
          "productType": {
            "type": "string",
            "description": "Categorizes the product as either a one-time purchase or a recurring charge.",
            "example": "ONE_TIME",
            "enum": [
              "ONE_TIME",
              "SUBSCRIPTION_PLAN"
            ]
          },
          "removable": {
            "type": "boolean",
            "description": "Indicates `true` if the interface should allow the buyer to remove the item from the cart.",
            "example": true
          },
          "bundle": {
            "type": "boolean",
            "description": "Indicates `true` if the item serves as a parent bundle containing sub-products.",
            "example": false
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "productFormat": {
            "type": "string",
            "description": "Categorizes the fulfillment logic format of the product.",
            "example": "DIGITAL",
            "enum": [
              "DIGITAL",
              "PHYSICAL"
            ]
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata associated with the line item.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscription": {
            "type": "object",
            "description": "The active subscription configurations appended to the item, including defined schedules and notification intervals.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        }
      },
      "CartResponse": {
        "type": "object",
        "description": "Details the active state of the cart, providing a list of line items and the finalized calculated totals for the entire session.",
        "properties": {
          "couponCode": {
            "type": "string",
            "description": "The specific promotional coupon code evaluated by the session.",
            "example": "10_OFF"
          },
          "couponHasApplied": {
            "type": "boolean",
            "description": "Indicates `true` if the submitted `couponCode` successfully validated against the cart's line items and triggered a discount.",
            "example": true
          },
          "lineItems": {
            "type": "array",
            "description": "The list of calculated product items currently residing in the session.",
            "items": {
              "$ref": "#/components/schemas/OrderItemResponse"
            }
          },
          "netTotal": {
            "type": "number",
            "description": "The grand total calculated for the cart, factoring in all applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 90
          },
          "netTotalDisplay": {
            "type": "string",
            "description": "The `netTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "listTotal": {
            "type": "number",
            "description": "The subtotal calculated for the cart, ignoring applied discounts. Includes or excludes taxes based on the value of `taxIncluded`.",
            "example": 100
          },
          "listTotalDisplay": {
            "type": "string",
            "description": "The `listTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in both applied discounts and evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 90
          },
          "withTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withTaxNetTotal` mapped to a localized currency string.",
            "example": "$90.00"
          },
          "withTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts but factoring in evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 100
          },
          "withTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withTaxListTotal` mapped to a localized currency string.",
            "example": "$100.00"
          },
          "withoutTaxNetTotal": {
            "type": "number",
            "description": "A forced-calculation total factoring in applied discounts but stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 70
          },
          "withoutTaxNetTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxNetTotal` mapped to a localized currency string.",
            "example": "$70.00"
          },
          "withoutTaxListTotal": {
            "type": "number",
            "description": "A forced-calculation subtotal ignoring applied discounts and stripping out all evaluated taxes, regardless of the `taxIncluded` setting.",
            "example": 80
          },
          "withoutTaxListTotalDisplay": {
            "type": "string",
            "description": "The `withoutTaxListTotal` mapped to a localized currency string.",
            "example": "$80.00"
          },
          "discountTotal": {
            "type": "number",
            "description": "The absolute sum of all discounts (both product and coupon) deducted from the cart.",
            "example": 10
          },
          "discountTotalDisplay": {
            "type": "string",
            "description": "The `discountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "couponDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by an evaluated coupon.",
            "example": 10
          },
          "couponDiscountTotalDisplay": {
            "type": "string",
            "description": "The `couponDiscountTotal` mapped to a localized currency string.",
            "example": "$10.00"
          },
          "productDiscountTotal": {
            "type": "number",
            "description": "The specific absolute sum discounted explicitly by configured volume or catalog product discounts.",
            "example": 0
          },
          "productDiscountTotalDisplay": {
            "type": "string",
            "description": "The `productDiscountTotal` mapped to a localized currency string.",
            "example": "$0.00"
          },
          "taxIncluded": {
            "type": "string",
            "description": "Indicates the tax calculation mode applied to the returned core price fields.",
            "example": "TAXES_INCLUDED_IN_PRICE",
            "enum": [
              "TAXES_INCLUDED_IN_PRICE",
              "TAXES_ADDED_TO_PRICE"
            ]
          },
          "taxTotal": {
            "type": "number",
            "description": "The absolute sum of all taxes calculated across the cart.",
            "example": 20
          },
          "taxTotalDisplay": {
            "type": "string",
            "description": "The `taxTotal` mapped to a localized currency string.",
            "example": "$20.00"
          },
          "taxRate": {
            "type": "number",
            "description": "The effective tax rate percentage calculated across the cart based on the buyer's location.",
            "example": 28.57
          }
        }
      },
      "PaymentMethod": {
        "type": "object",
        "description": "Maps the supported configurations and requirements for a valid payment method channel.",
        "properties": {
          "id": {
            "$ref": "#/components/schemas/PaymentMethodType"
          },
          "description": {
            "type": "string",
            "description": "The internal system key or localization reference used to map the payment method to its translated display name in the frontend interface.",
            "example": "PaymentMethodType.CreditCard",
            "maxLength": 255
          },
          "requiredFieldElements": {
            "$ref": "#/components/schemas/FieldElement"
          },
          "showFieldElements": {
            "$ref": "#/components/schemas/FieldElement",
            "description": "Show these fields to the buyer on the form. May not be required."
          },
          "supportedProductTypes": {
            "type": "array",
            "description": "A list defining whether the payment method supports one-time purchases, subscription plans, or both.",
            "example": "ONE_TIME",
            "items": {
              "type": "string",
              "enum": [
                "ONE_TIME",
                "SUBSCRIPTION_PLAN"
              ]
            }
          },
          "variants": {
            "type": "array",
            "description": "A list of explicitly supported card brands or sub-channels handled by this payment method.",
            "example": [
              "VISA",
              "MASTER_CARD",
              "AMEX"
            ],
            "items": {
              "type": "string",
              "enum": [
                "VISA",
                "MASTER_CARD",
                "AMEX",
                "ELO",
                "DISCOVER",
                "DINNERS",
                "UNION_PAY",
                "JCB",
                "HIPERCARD"
              ]
            }
          }
        }
      },
      "Warning": {
        "type": "object",
        "description": "Details non-fatal errors that occurred during session execution. Warnings permit the session to process successfully but flag ignored inputs or invalid configurations.",
        "properties": {
          "code": {
            "type": "string",
            "description": "A constant code mapping to the specific type of warning triggered. Used to drive conditional messaging in the frontend.",
            "example": "INVALID_PROMO_CODE",
            "enum": [
              "INVALID_PROMO_CODE",
              "INVALID_COUNTRY",
              "CHECKOUT_NOT_LIVE",
              "INVALID_TAX_ID",
              "INVALID_BUYER_IP",
              "INVALID_LOCALE",
              "INVALID_POSTAL_CODE"
            ]
          },
          "field": {
            "type": "string",
            "description": "The name of the specific input parameter or field that triggered the warning.",
            "example": "promoCode"
          },
          "message": {
            "type": "string",
            "description": "A human-readable description detailing the cause of the warning.",
            "example": "The promo code you entered is invalid and was not applied. Please check the code."
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Retrieve payment methods

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve payment methods

Retrieves the ordered list of payment methods available for the specified session and checkout. Automatically filters unsupported methods and localizes the options for the buyer.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}/payment-methods": {
      "get": {
        "tags": [
          "Session"
        ],
        "summary": "Retrieve payment methods",
        "description": "Retrieves the ordered list of payment methods available for the specified session and checkout. Automatically filters unsupported methods and localizes the options for the buyer.",
        "operationId": "retrievePaymentMethods",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Successfully retrieved the available payment methods.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PaymentMethodResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates an invalid session ID or checkout path.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "PaymentMethodType": {
        "type": "string",
        "description": "Identifies the specific payment method brand or channel.",
        "enum": [
          "ACH",
          "ALIPAY",
          "AMAZON",
          "APPLE_PAY",
          "CARD",
          "GOOGLE_PAY",
          "IDEAL",
          "KAKAOPAY",
          "KLARNA",
          "MERCADO_PAGO",
          "PAYPAL",
          "PIX",
          "SEPA",
          "TOSS",
          "UPI",
          "WECHAT_PAY",
          "WIRE",
          "QUOTE",
          "PURCHASE_ORDER",
          "UNKNOWN"
        ],
        "example": "CARD"
      },
      "FieldElement": {
        "type": "array",
        "description": "A list of specific buyer data points associated with a checkout interface form.",
        "items": {
          "type": "string",
          "enum": [
            "POSTAL_CODE",
            "NAME",
            "EMAIL",
            "PHONE",
            "COMPANY",
            "CPF",
            "BILLING_ADDRESS",
            "SHIPPING_ADDRESS",
            "ACH",
            "SEPA",
            "CARD"
          ]
        }
      },
      "PaymentMethod": {
        "type": "object",
        "description": "Maps the supported configurations and requirements for a valid payment method channel.",
        "properties": {
          "id": {
            "$ref": "#/components/schemas/PaymentMethodType"
          },
          "description": {
            "type": "string",
            "description": "The internal system key or localization reference used to map the payment method to its translated display name in the frontend interface.",
            "example": "PaymentMethodType.CreditCard",
            "maxLength": 255
          },
          "requiredFieldElements": {
            "$ref": "#/components/schemas/FieldElement"
          },
          "showFieldElements": {
            "$ref": "#/components/schemas/FieldElement",
            "description": "Show these fields to the buyer on the form. May not be required."
          },
          "supportedProductTypes": {
            "type": "array",
            "description": "A list defining whether the payment method supports one-time purchases, subscription plans, or both.",
            "example": "ONE_TIME",
            "items": {
              "type": "string",
              "enum": [
                "ONE_TIME",
                "SUBSCRIPTION_PLAN"
              ]
            }
          },
          "variants": {
            "type": "array",
            "description": "A list of explicitly supported card brands or sub-channels handled by this payment method.",
            "example": [
              "VISA",
              "MASTER_CARD",
              "AMEX"
            ],
            "items": {
              "type": "string",
              "enum": [
                "VISA",
                "MASTER_CARD",
                "AMEX",
                "ELO",
                "DISCOVER",
                "DINNERS",
                "UNION_PAY",
                "JCB",
                "HIPERCARD"
              ]
            }
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      },
      "PaymentMethodResponse": {
        "type": "object",
        "description": "Wraps a collection of active payment methods. Unsupported options are inherently stripped.",
        "properties": {
          "paymentMethods": {
            "type": "array",
            "description": "The list of payment methods validated and cleared for presentation.",
            "items": {
              "$ref": "#/components/schemas/PaymentMethod"
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Add session item

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Add session item

Appends a new product item to the cart of an existing order session.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}/cart/items": {
      "post": {
        "tags": [
          "Session"
        ],
        "summary": "Add session item",
        "description": "Appends a new product item to the cart of an existing order session.",
        "operationId": "addSessionItem",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/OrderItemRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Successfully added the item to the session.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/OrderItemResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates invalid input properties.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "OrderItemRequest": {
        "type": "object",
        "description": "Details a specific line item being added or modified within a cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the catalog product to add.",
            "example": "gold-tier",
            "maxLength": 256
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units to purchase. Defaults to 1 if omitted.",
            "example": 1
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout. Requires an authenticated request.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The default quantity presented at checkout. Requires an authenticated request.",
            "example": 1
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "customPrice": {
            "type": "object",
            "description": "Overrides the base catalog price with a custom defined pricing structure. Requires an authenticated request.",
            "properties": {
              "unitPrice": {
                "description": "The custom flat price mapping applied to a single unit.",
                "$ref": "#/components/schemas/PriceMap"
              },
              "discounts": {
                "type": "array",
                "description": "A list of volume-based discounting tiers. Applicable ranges must not overlap.",
                "items": {
                  "type": "object",
                  "properties": {
                    "minQuantity": {
                      "type": "integer",
                      "description": "The minimum volume of units required to trigger this discount tier. Defaults to `1`.",
                      "example": 2
                    },
                    "amountDiscount": {
                      "$ref": "#/components/schemas/PriceMap",
                      "description": "A fixed flat amount deducted per unit within this tier."
                    },
                    "percentDiscount": {
                      "type": "integer",
                      "description": "A percentage amount deducted per unit within this tier.",
                      "example": 10
                    }
                  }
                }
              },
              "discountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods this discount persists (only applicable to subscription plans; does not apply to one-time products).",
                "example": 3
              },
              "setupFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed setup fee applied exclusively to subscription products."
              },
              "edsFee": {
                "description": "Define the price the buyer pays for the Extended Download Service (EDS), which extends digital file access from 7 days to 1 year.\n\nThis fee applies once per order and must exceed the $1.50 base fee charged by FastSpring.\n",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/PriceMap"
                  }
                ]
              },
              "shippingFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed shipping fee applied to physical goods."
              }
            }
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata tied specifically to this order item. Requires an authenticated request.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscriptionOverrides": {
            "type": "object",
            "description": "Custom subscription configurations that override the base catalog setup for this specific line item. Requires an authenticated request.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        },
        "required": [
          "productPath"
        ]
      },
      "LanguageMap": {
        "type": "object",
        "description": "A map of localized strings based on ISO 639-1 language codes.",
        "properties": {
          "ar": {
            "type": "string",
            "example": "Arabic",
            "maxLength": 512
          },
          "cs": {
            "type": "string",
            "example": "Czech",
            "maxLength": 512
          },
          "da": {
            "type": "string",
            "example": "Danish",
            "maxLength": 512
          },
          "de": {
            "type": "string",
            "example": "German",
            "maxLength": 512
          },
          "es": {
            "type": "string",
            "example": "Spanish",
            "maxLength": 512
          },
          "en": {
            "type": "string",
            "example": "English",
            "maxLength": 512
          },
          "fi": {
            "type": "string",
            "example": "Finnish",
            "maxLength": 512
          },
          "fr": {
            "type": "string",
            "example": "French",
            "maxLength": 512
          },
          "hr": {
            "type": "string",
            "example": "Croatian",
            "maxLength": 512
          },
          "it": {
            "type": "string",
            "example": "Italian",
            "maxLength": 512
          },
          "iw": {
            "type": "string",
            "example": "Hebrew",
            "maxLength": 512
          },
          "ja": {
            "type": "string",
            "example": "Japanese",
            "maxLength": 512
          },
          "ko": {
            "type": "string",
            "example": "Korean",
            "maxLength": 512
          },
          "nl": {
            "type": "string",
            "example": "Dutch",
            "maxLength": 512
          },
          "no": {
            "type": "string",
            "example": "Norwegian",
            "maxLength": 512
          },
          "pl": {
            "type": "string",
            "example": "Polish",
            "maxLength": 512
          },
          "pt": {
            "type": "string",
            "example": "Portuguese",
            "maxLength": 512
          },
          "ru": {
            "type": "string",
            "example": "Russian",
            "maxLength": 512
          },
          "sk": {
            "type": "string",
            "example": "Slovak",
            "maxLength": 512
          },
          "sv": {
            "type": "string",
            "example": "Swedish",
            "maxLength": 512
          },
          "tr": {
            "type": "string",
            "example": "Turkish",
            "maxLength": 512
          },
          "zh": {
            "type": "string",
            "example": "Chinese",
            "maxLength": 512
          }
        }
      },
      "ProductDescription": {
        "type": "object",
        "description": "Specifies localized descriptive fields for a product. Requires an authenticated request to modify.",
        "properties": {
          "display": {
            "description": "The primary title of the product presented to the buyer.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "instructions": {
            "description": "The post-purchase instructions displayed for the product.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "summary": {
            "description": "A summary description appearing under the main product title.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "imageUrl": {
            "type": "string",
            "description": "The absolute URL of the product's primary image.",
            "example": "https://cdn.onfastspring.com/themes/images/default-store-logo.png"
          }
        }
      },
      "PriceMap": {
        "type": "object",
        "description": "A map of fixed prices across supported currencies.",
        "properties": {
          "AED": {
            "type": "number",
            "example": 10
          },
          "ARS": {
            "type": "number",
            "example": 10
          },
          "AUD": {
            "type": "number",
            "example": 10
          },
          "BRL": {
            "type": "number",
            "example": 10
          },
          "CAD": {
            "type": "number",
            "example": 10
          },
          "CHF": {
            "type": "number",
            "example": 10
          },
          "CLP": {
            "type": "number",
            "example": 10
          },
          "CNY": {
            "type": "number",
            "example": 10
          },
          "COP": {
            "type": "number",
            "example": 10
          },
          "CZK": {
            "type": "number",
            "example": 10
          },
          "DKK": {
            "type": "number",
            "example": 10
          },
          "EUR": {
            "type": "number",
            "example": 10
          },
          "GBP": {
            "type": "number",
            "example": 10
          },
          "HKD": {
            "type": "number",
            "example": 10
          },
          "HUF": {
            "type": "number",
            "example": 10
          },
          "IDR": {
            "type": "number",
            "example": 10
          },
          "INR": {
            "type": "number",
            "example": 10
          },
          "JPY": {
            "type": "number",
            "example": 10
          },
          "KRW": {
            "type": "number",
            "example": 10
          },
          "MXN": {
            "type": "number",
            "example": 10
          },
          "MYR": {
            "type": "number",
            "example": 10
          },
          "NOK": {
            "type": "number",
            "example": 10
          },
          "NZD": {
            "type": "number",
            "example": 10
          },
          "PEN": {
            "type": "number",
            "example": 10
          },
          "PHP": {
            "type": "number",
            "example": 10
          },
          "PLN": {
            "type": "number",
            "example": 10
          },
          "RUB": {
            "type": "number",
            "example": 10
          },
          "SAR": {
            "type": "number",
            "example": 10
          },
          "SEK": {
            "type": "number",
            "example": 10
          },
          "SGD": {
            "type": "number",
            "example": 10
          },
          "THB": {
            "type": "number",
            "example": 10
          },
          "TRY": {
            "type": "number",
            "example": 10
          },
          "TWD": {
            "type": "number",
            "example": 10
          },
          "USD": {
            "type": "number",
            "example": 10
          },
          "VND": {
            "type": "number",
            "example": 10
          },
          "ZAR": {
            "type": "number",
            "example": 10
          }
        }
      },
      "IntervalUnit": {
        "type": "string",
        "description": "The unit of time defining a billing or reminder interval. Evaluated in conjunction with an interval length.",
        "enum": [
          "DAY",
          "WEEK",
          "MONTH",
          "YEAR",
          "ON_DEMAND"
        ],
        "example": "MONTH"
      },
      "Interval": {
        "type": "object",
        "description": "Defines the frequency of a recurring billing cycle or scheduled notification.",
        "properties": {
          "intervalUnit": {
            "$ref": "#/components/schemas/IntervalUnit"
          },
          "intervalLength": {
            "type": "integer",
            "description": "The number of units defining the interval.",
            "example": 1
          },
          "intervalCount": {
            "type": "integer",
            "description": "The total number of consecutive intervals that make up this plan or sequence.",
            "example": 1
          }
        }
      },
      "SubscriptionAttribute": {
        "type": "object",
        "description": "Defines configuration attributes specific to subscription products. Requires an authenticated request to modify.",
        "properties": {
          "billingFrequency": {
            "description": "The recurring interval at which the subscription bills.",
            "$ref": "#/components/schemas/Interval"
          },
          "trialDays": {
            "type": "integer",
            "description": "The number of free trial days before the first billing cycle occurs.",
            "example": 14
          }
        }
      },
      "OrderItemResponse": {
        "type": "object",
        "description": "Details the state and calculated pricing for a specific line item within the active cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the selected product.",
            "example": "gold-tier"
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units configured for purchase.",
            "example": 2
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The original default quantity associated with the item before any modifications.",
            "example": 1
          },
          "virtualProduct": {
            "type": "boolean",
            "description": "Indicates `true` if the item was dynamically constructed in the request and does not map to a saved catalog product.",
            "example": false
          },
          "price": {
            "type": "object",
            "description": "Contains all calculated price elements for the line item including discounts, totals, and localized formatting.",
            "properties": {
              "unitNetPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 90
              },
              "unitNetPriceDisplay": {
                "type": "string",
                "description": "The `unitNetPrice` mapped to a localized currency string.",
                "example": "$90.00"
              },
              "unitListPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, ignoring any applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 100
              },
              "unitListPriceDisplay": {
                "type": "string",
                "description": "The `unitListPrice` mapped to a localized currency string.",
                "example": "$100.00"
              },
              "unitDiscount": {
                "type": "number",
                "description": "The absolute discount value deducted from 1 unit.",
                "example": 10
              },
              "unitDiscountDisplay": {
                "type": "string",
                "description": "The `unitDiscount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTotalDiscount": {
                "type": "number",
                "description": "The absolute total discount value calculated across all units (`unitDiscount` * `quantity`).",
                "example": 20
              },
              "extendedTotalDiscountDisplay": {
                "type": "string",
                "description": "The `extendedTotalDiscount` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "extendedNetTotal": {
                "type": "number",
                "description": "The final, grand total calculated for all units, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 180
              },
              "extendedNetTotalDisplay": {
                "type": "string",
                "description": "The `extendedNetTotal` mapped to a localized currency string.",
                "example": "$180.00"
              },
              "extendedListTotal": {
                "type": "number",
                "description": "The subtotal calculated for all units, ignoring applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 200
              },
              "extendedListTotalDisplay": {
                "type": "string",
                "description": "The `extendedListTotal` mapped to a localized currency string.",
                "example": "$200.00"
              },
              "taxIncluded": {
                "type": "string",
                "description": "Indicates the tax calculation mode applied to the returned price fields.",
                "enum": [
                  "TAXES_INCLUDED_IN_PRICE",
                  "TAXES_ADDED_TO_PRICE"
                ],
                "example": "TAXES_INCLUDED_IN_PRICE"
              },
              "unitTaxAmount": {
                "type": "number",
                "description": "The absolute tax amount calculated for exactly 1 unit.",
                "example": 10
              },
              "unitTaxAmountDisplay": {
                "type": "string",
                "description": "The `unitTaxAmount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTaxTotal": {
                "type": "number",
                "description": "The absolute total tax amount calculated across all units (`unitTaxAmount` * `quantity`).",
                "example": 20
              },
              "extendedTaxTotalDisplay": {
                "type": "string",
                "description": "The `extendedTaxTotal` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "taxPercent": {
                "type": "number",
                "description": "The effective tax rate percentage calculated for the item based on the buyer's location and tax status.",
                "example": 10
              },
              "taxExempt": {
                "type": "boolean",
                "description": "Indicates `true` if the buyer qualifies for zero-rated taxes based on an evaluated `taxId`.",
                "example": false
              },
              "productDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a product-level discount will be applied.",
                "example": 100
              },
              "couponDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a coupon-level discount will be applied.",
                "example": 100
              }
            }
          },
          "productType": {
            "type": "string",
            "description": "Categorizes the product as either a one-time purchase or a recurring charge.",
            "example": "ONE_TIME",
            "enum": [
              "ONE_TIME",
              "SUBSCRIPTION_PLAN"
            ]
          },
          "removable": {
            "type": "boolean",
            "description": "Indicates `true` if the interface should allow the buyer to remove the item from the cart.",
            "example": true
          },
          "bundle": {
            "type": "boolean",
            "description": "Indicates `true` if the item serves as a parent bundle containing sub-products.",
            "example": false
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "productFormat": {
            "type": "string",
            "description": "Categorizes the fulfillment logic format of the product.",
            "example": "DIGITAL",
            "enum": [
              "DIGITAL",
              "PHYSICAL"
            ]
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata associated with the line item.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscription": {
            "type": "object",
            "description": "The active subscription configurations appended to the item, including defined schedules and notification intervals.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Remove session item

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Remove session item

Removes an existing product item from the session cart.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}/cart/items/{productPath}": {
      "delete": {
        "tags": [
          "Session"
        ],
        "summary": "Remove session item",
        "description": "Removes an existing product item from the session cart.",
        "operationId": "removeSessionItem",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          },
          {
            "in": "path",
            "name": "productPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "The unique identifier of the product to remove.",
              "example": "gold-tier"
            }
          }
        ],
        "responses": {
          "204": {
            "description": "Successfully removed the item from the session. No content returned."
          },
          "400": {
            "description": "Bad request. Indicates the product could not be found or invalid input.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Update session item

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update session item

Updates an existing product item in the session cart. Typically used to modify quantities or apply custom pricing. Requires an authenticated request to modify pricing.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}/cart/items/{productPath}": {
      "put": {
        "tags": [
          "Session"
        ],
        "summary": "Update session item",
        "description": "Updates an existing product item in the session cart. Typically used to modify quantities or apply custom pricing. Requires an authenticated request to modify pricing.",
        "operationId": "updateSessionItem",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          },
          {
            "in": "path",
            "name": "productPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "The unique identifier of the product to update.\n> **Note:** You can update multiple products simultaneously by listing them in the path as a comma-separated string (e.g., `gold-tier,silver-tier`).\n",
              "example": "gold-tier,silver-tier"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/OrderItemRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Successfully updated the item in the session.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/OrderItemResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates invalid input properties or values.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "OrderItemRequest": {
        "type": "object",
        "description": "Details a specific line item being added or modified within a cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the catalog product to add.",
            "example": "gold-tier",
            "maxLength": 256
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units to purchase. Defaults to 1 if omitted.",
            "example": 1
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout. Requires an authenticated request.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The default quantity presented at checkout. Requires an authenticated request.",
            "example": 1
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "customPrice": {
            "type": "object",
            "description": "Overrides the base catalog price with a custom defined pricing structure. Requires an authenticated request.",
            "properties": {
              "unitPrice": {
                "description": "The custom flat price mapping applied to a single unit.",
                "$ref": "#/components/schemas/PriceMap"
              },
              "discounts": {
                "type": "array",
                "description": "A list of volume-based discounting tiers. Applicable ranges must not overlap.",
                "items": {
                  "type": "object",
                  "properties": {
                    "minQuantity": {
                      "type": "integer",
                      "description": "The minimum volume of units required to trigger this discount tier. Defaults to `1`.",
                      "example": 2
                    },
                    "amountDiscount": {
                      "$ref": "#/components/schemas/PriceMap",
                      "description": "A fixed flat amount deducted per unit within this tier."
                    },
                    "percentDiscount": {
                      "type": "integer",
                      "description": "A percentage amount deducted per unit within this tier.",
                      "example": 10
                    }
                  }
                }
              },
              "discountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods this discount persists (only applicable to subscription plans; does not apply to one-time products).",
                "example": 3
              },
              "setupFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed setup fee applied exclusively to subscription products."
              },
              "edsFee": {
                "description": "Define the price the buyer pays for the Extended Download Service (EDS), which extends digital file access from 7 days to 1 year.\n\nThis fee applies once per order and must exceed the $1.50 base fee charged by FastSpring.\n",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/PriceMap"
                  }
                ]
              },
              "shippingFee": {
                "$ref": "#/components/schemas/PriceMap",
                "description": "A fixed shipping fee applied to physical goods."
              }
            }
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata tied specifically to this order item. Requires an authenticated request.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscriptionOverrides": {
            "type": "object",
            "description": "Custom subscription configurations that override the base catalog setup for this specific line item. Requires an authenticated request.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        },
        "required": [
          "productPath"
        ]
      },
      "LanguageMap": {
        "type": "object",
        "description": "A map of localized strings based on ISO 639-1 language codes.",
        "properties": {
          "ar": {
            "type": "string",
            "example": "Arabic",
            "maxLength": 512
          },
          "cs": {
            "type": "string",
            "example": "Czech",
            "maxLength": 512
          },
          "da": {
            "type": "string",
            "example": "Danish",
            "maxLength": 512
          },
          "de": {
            "type": "string",
            "example": "German",
            "maxLength": 512
          },
          "es": {
            "type": "string",
            "example": "Spanish",
            "maxLength": 512
          },
          "en": {
            "type": "string",
            "example": "English",
            "maxLength": 512
          },
          "fi": {
            "type": "string",
            "example": "Finnish",
            "maxLength": 512
          },
          "fr": {
            "type": "string",
            "example": "French",
            "maxLength": 512
          },
          "hr": {
            "type": "string",
            "example": "Croatian",
            "maxLength": 512
          },
          "it": {
            "type": "string",
            "example": "Italian",
            "maxLength": 512
          },
          "iw": {
            "type": "string",
            "example": "Hebrew",
            "maxLength": 512
          },
          "ja": {
            "type": "string",
            "example": "Japanese",
            "maxLength": 512
          },
          "ko": {
            "type": "string",
            "example": "Korean",
            "maxLength": 512
          },
          "nl": {
            "type": "string",
            "example": "Dutch",
            "maxLength": 512
          },
          "no": {
            "type": "string",
            "example": "Norwegian",
            "maxLength": 512
          },
          "pl": {
            "type": "string",
            "example": "Polish",
            "maxLength": 512
          },
          "pt": {
            "type": "string",
            "example": "Portuguese",
            "maxLength": 512
          },
          "ru": {
            "type": "string",
            "example": "Russian",
            "maxLength": 512
          },
          "sk": {
            "type": "string",
            "example": "Slovak",
            "maxLength": 512
          },
          "sv": {
            "type": "string",
            "example": "Swedish",
            "maxLength": 512
          },
          "tr": {
            "type": "string",
            "example": "Turkish",
            "maxLength": 512
          },
          "zh": {
            "type": "string",
            "example": "Chinese",
            "maxLength": 512
          }
        }
      },
      "ProductDescription": {
        "type": "object",
        "description": "Specifies localized descriptive fields for a product. Requires an authenticated request to modify.",
        "properties": {
          "display": {
            "description": "The primary title of the product presented to the buyer.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "instructions": {
            "description": "The post-purchase instructions displayed for the product.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "summary": {
            "description": "A summary description appearing under the main product title.",
            "$ref": "#/components/schemas/LanguageMap"
          },
          "imageUrl": {
            "type": "string",
            "description": "The absolute URL of the product's primary image.",
            "example": "https://cdn.onfastspring.com/themes/images/default-store-logo.png"
          }
        }
      },
      "PriceMap": {
        "type": "object",
        "description": "A map of fixed prices across supported currencies.",
        "properties": {
          "AED": {
            "type": "number",
            "example": 10
          },
          "ARS": {
            "type": "number",
            "example": 10
          },
          "AUD": {
            "type": "number",
            "example": 10
          },
          "BRL": {
            "type": "number",
            "example": 10
          },
          "CAD": {
            "type": "number",
            "example": 10
          },
          "CHF": {
            "type": "number",
            "example": 10
          },
          "CLP": {
            "type": "number",
            "example": 10
          },
          "CNY": {
            "type": "number",
            "example": 10
          },
          "COP": {
            "type": "number",
            "example": 10
          },
          "CZK": {
            "type": "number",
            "example": 10
          },
          "DKK": {
            "type": "number",
            "example": 10
          },
          "EUR": {
            "type": "number",
            "example": 10
          },
          "GBP": {
            "type": "number",
            "example": 10
          },
          "HKD": {
            "type": "number",
            "example": 10
          },
          "HUF": {
            "type": "number",
            "example": 10
          },
          "IDR": {
            "type": "number",
            "example": 10
          },
          "INR": {
            "type": "number",
            "example": 10
          },
          "JPY": {
            "type": "number",
            "example": 10
          },
          "KRW": {
            "type": "number",
            "example": 10
          },
          "MXN": {
            "type": "number",
            "example": 10
          },
          "MYR": {
            "type": "number",
            "example": 10
          },
          "NOK": {
            "type": "number",
            "example": 10
          },
          "NZD": {
            "type": "number",
            "example": 10
          },
          "PEN": {
            "type": "number",
            "example": 10
          },
          "PHP": {
            "type": "number",
            "example": 10
          },
          "PLN": {
            "type": "number",
            "example": 10
          },
          "RUB": {
            "type": "number",
            "example": 10
          },
          "SAR": {
            "type": "number",
            "example": 10
          },
          "SEK": {
            "type": "number",
            "example": 10
          },
          "SGD": {
            "type": "number",
            "example": 10
          },
          "THB": {
            "type": "number",
            "example": 10
          },
          "TRY": {
            "type": "number",
            "example": 10
          },
          "TWD": {
            "type": "number",
            "example": 10
          },
          "USD": {
            "type": "number",
            "example": 10
          },
          "VND": {
            "type": "number",
            "example": 10
          },
          "ZAR": {
            "type": "number",
            "example": 10
          }
        }
      },
      "IntervalUnit": {
        "type": "string",
        "description": "The unit of time defining a billing or reminder interval. Evaluated in conjunction with an interval length.",
        "enum": [
          "DAY",
          "WEEK",
          "MONTH",
          "YEAR",
          "ON_DEMAND"
        ],
        "example": "MONTH"
      },
      "Interval": {
        "type": "object",
        "description": "Defines the frequency of a recurring billing cycle or scheduled notification.",
        "properties": {
          "intervalUnit": {
            "$ref": "#/components/schemas/IntervalUnit"
          },
          "intervalLength": {
            "type": "integer",
            "description": "The number of units defining the interval.",
            "example": 1
          },
          "intervalCount": {
            "type": "integer",
            "description": "The total number of consecutive intervals that make up this plan or sequence.",
            "example": 1
          }
        }
      },
      "SubscriptionAttribute": {
        "type": "object",
        "description": "Defines configuration attributes specific to subscription products. Requires an authenticated request to modify.",
        "properties": {
          "billingFrequency": {
            "description": "The recurring interval at which the subscription bills.",
            "$ref": "#/components/schemas/Interval"
          },
          "trialDays": {
            "type": "integer",
            "description": "The number of free trial days before the first billing cycle occurs.",
            "example": 14
          }
        }
      },
      "OrderItemResponse": {
        "type": "object",
        "description": "Details the state and calculated pricing for a specific line item within the active cart.",
        "properties": {
          "productPath": {
            "type": "string",
            "description": "The unique identifier of the selected product.",
            "example": "gold-tier"
          },
          "quantity": {
            "type": "integer",
            "description": "The total volume of units configured for purchase.",
            "example": 2
          },
          "quantityBehavior": {
            "type": "string",
            "description": "Indicates whether the buyer is allowed to modify the item quantity during checkout.",
            "example": "ALLOW"
          },
          "quantityDefault": {
            "type": "integer",
            "description": "The original default quantity associated with the item before any modifications.",
            "example": 1
          },
          "virtualProduct": {
            "type": "boolean",
            "description": "Indicates `true` if the item was dynamically constructed in the request and does not map to a saved catalog product.",
            "example": false
          },
          "price": {
            "type": "object",
            "description": "Contains all calculated price elements for the line item including discounts, totals, and localized formatting.",
            "properties": {
              "unitNetPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 90
              },
              "unitNetPriceDisplay": {
                "type": "string",
                "description": "The `unitNetPrice` mapped to a localized currency string.",
                "example": "$90.00"
              },
              "unitListPrice": {
                "type": "number",
                "description": "The base price calculated for exactly 1 unit, ignoring any applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 100
              },
              "unitListPriceDisplay": {
                "type": "string",
                "description": "The `unitListPrice` mapped to a localized currency string.",
                "example": "$100.00"
              },
              "unitDiscount": {
                "type": "number",
                "description": "The absolute discount value deducted from 1 unit.",
                "example": 10
              },
              "unitDiscountDisplay": {
                "type": "string",
                "description": "The `unitDiscount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTotalDiscount": {
                "type": "number",
                "description": "The absolute total discount value calculated across all units (`unitDiscount` * `quantity`).",
                "example": 20
              },
              "extendedTotalDiscountDisplay": {
                "type": "string",
                "description": "The `extendedTotalDiscount` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "extendedNetTotal": {
                "type": "number",
                "description": "The final, grand total calculated for all units, factoring in applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 180
              },
              "extendedNetTotalDisplay": {
                "type": "string",
                "description": "The `extendedNetTotal` mapped to a localized currency string.",
                "example": "$180.00"
              },
              "extendedListTotal": {
                "type": "number",
                "description": "The subtotal calculated for all units, ignoring applied discounts. Includes taxes if `taxIncluded` dictates inclusive pricing.",
                "example": 200
              },
              "extendedListTotalDisplay": {
                "type": "string",
                "description": "The `extendedListTotal` mapped to a localized currency string.",
                "example": "$200.00"
              },
              "taxIncluded": {
                "type": "string",
                "description": "Indicates the tax calculation mode applied to the returned price fields.",
                "enum": [
                  "TAXES_INCLUDED_IN_PRICE",
                  "TAXES_ADDED_TO_PRICE"
                ],
                "example": "TAXES_INCLUDED_IN_PRICE"
              },
              "unitTaxAmount": {
                "type": "number",
                "description": "The absolute tax amount calculated for exactly 1 unit.",
                "example": 10
              },
              "unitTaxAmountDisplay": {
                "type": "string",
                "description": "The `unitTaxAmount` mapped to a localized currency string.",
                "example": "$10.00"
              },
              "extendedTaxTotal": {
                "type": "number",
                "description": "The absolute total tax amount calculated across all units (`unitTaxAmount` * `quantity`).",
                "example": 20
              },
              "extendedTaxTotalDisplay": {
                "type": "string",
                "description": "The `extendedTaxTotal` mapped to a localized currency string.",
                "example": "$20.00"
              },
              "taxPercent": {
                "type": "number",
                "description": "The effective tax rate percentage calculated for the item based on the buyer's location and tax status.",
                "example": 10
              },
              "taxExempt": {
                "type": "boolean",
                "description": "Indicates `true` if the buyer qualifies for zero-rated taxes based on an evaluated `taxId`.",
                "example": false
              },
              "productDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a product-level discount will be applied.",
                "example": 100
              },
              "couponDiscountDuration": {
                "type": "integer",
                "description": "The total number of consecutive billing periods a coupon-level discount will be applied.",
                "example": 100
              }
            }
          },
          "productType": {
            "type": "string",
            "description": "Categorizes the product as either a one-time purchase or a recurring charge.",
            "example": "ONE_TIME",
            "enum": [
              "ONE_TIME",
              "SUBSCRIPTION_PLAN"
            ]
          },
          "removable": {
            "type": "boolean",
            "description": "Indicates `true` if the interface should allow the buyer to remove the item from the cart.",
            "example": true
          },
          "bundle": {
            "type": "boolean",
            "description": "Indicates `true` if the item serves as a parent bundle containing sub-products.",
            "example": false
          },
          "descriptions": {
            "$ref": "#/components/schemas/ProductDescription"
          },
          "productFormat": {
            "type": "string",
            "description": "Categorizes the fulfillment logic format of the product.",
            "example": "DIGITAL",
            "enum": [
              "DIGITAL",
              "PHYSICAL"
            ]
          },
          "attributes": {
            "type": "object",
            "description": "A key-value map of custom metadata associated with the line item.",
            "example": {
              "licenseKey": "ABC-123"
            }
          },
          "subscription": {
            "type": "object",
            "description": "The active subscription configurations appended to the item, including defined schedules and notification intervals.",
            "$ref": "#/components/schemas/SubscriptionAttribute"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```

Update session customer

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update session customer

Updates specific customer and billing information on an existing session. Provides a reduced scope update compared to modifying the entire session payload.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "Session Service API",
    "version": "1.0.0",
    "description": "Session Service API for creating order sessions"
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "tags": [
    {
      "name": "Session",
      "description": "APIs for creating order sessions"
    }
  ],
  "paths": {
    "/v2/checkouts/{checkoutPath}/sessions/{sessionId}/customer": {
      "put": {
        "tags": [
          "Session"
        ],
        "summary": "Update session customer",
        "description": "Updates specific customer and billing information on an existing session. Provides a reduced scope update compared to modifying the entire session payload.",
        "operationId": "updateSessionCustomer",
        "parameters": [
          {
            "in": "path",
            "name": "checkoutPath",
            "required": true,
            "schema": {
              "type": "string",
              "description": "\nThe unique identifier for the checkout instance, in the format `store-id/checkout-id` (e.g., `fastspring/main`).\n> **Note:** Target a specific checkout path to route the session to a single buyer experience and ensure accurate pricing. Stores frequently run multiple checkout variations simultaneously.\n",
              "example": "fastspring/main"
            }
          },
          {
            "in": "path",
            "name": "sessionId",
            "required": true,
            "schema": {
              "type": "string",
              "maxLength": 64,
              "description": "The unique identifier of the order session.",
              "example": "OS123456789012345ABC"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CustomerRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Successfully updated the customer details.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CustomerResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Indicates invalid input properties or values.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ErrorResponse"
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
      "none": {
        "type": "http",
        "scheme": "none"
      },
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      },
      "oauth2": {
        "type": "oauth2",
        "flows": {
          "authorizationCode": {
            "tokenUrl": "https://login.fastspring.com/identity/oauth/token",
            "authorizationUrl": "https://login.fastspring.com/identity/oauth/token",
            "scopes": {
              "ROLE_SELLER_API_READ_WRITE": "Seller API access",
              "ROLE_SESSION_SERVICE_WRITE": "Internal role. FS only.",
              "NONE": "Buyer access. No auth"
            }
          }
        }
      }
    },
    "schemas": {
      "CustomerRequest": {
        "type": "object",
        "description": "Specifies the customer and billing information applied to the order session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account. Requires an authenticated request.",
            "example": "ABC1234567890",
            "maxLength": 64
          },
          "externalAccountId": {
            "type": "string",
            "description": "An external ID used to link the buyer to your internal systems. Requires an authenticated request.",
            "example": "EXT-98765",
            "minLength": 4,
            "maxLength": 512,
            "format": "regex",
            "pattern": "^[_a-zA-Z0-9-]+$"
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContact"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddress"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContact"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddress"
          },
          "shipToType": {
            "type": "string",
            "description": "Identifies the type of shipping destination mapping applied to the order.",
            "example": "SAME_AS_BILL_TO",
            "enum": [
              "GIFT_PURCHASE",
              "SHIP_TO",
              "SAME_AS_BILL_TO"
            ]
          },
          "accountTags": {
            "type": "object",
            "additionalProperties": {
              "type": "string"
            },
            "description": "A key-value map of custom tags applied to the buyer's account. Requires an authenticated request.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "Capture the buyer's VAT, GST, or CPF identification number used for tax calculation or exemption. Required for buyers located in Brazil.",
            "example": "DE123456789",
            "maxLength": 255
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA",
            "maxLength": 2
          }
        }
      },
      "BillToContact": {
        "type": "object",
        "description": "Capture the billing contact details used to process the payment, calculate localized taxes, and generate the invoice. Pass standard, single-buyer information here, as this acts as the primary contact for the order.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContact": {
        "type": "object",
        "description": "Capture the recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddress": {
        "type": "object",
        "description": "Capture the physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddress": {
        "type": "object",
        "description": "Capture the physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "BillToContactResponse": {
        "type": "object",
        "description": "The billing contact details used to process the payment, calculate localized taxes, and generate the invoice.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "ShipToContactResponse": {
        "type": "object",
        "description": "The recipient's contact and delivery details to ensure accurate fulfillment.",
        "properties": {
          "email": {
            "type": "string",
            "description": "The buyer's email address.",
            "example": "support@fastspring.com",
            "maxLength": 255
          },
          "firstName": {
            "type": "string",
            "description": "The buyer's first name.",
            "example": "John",
            "maxLength": 50
          },
          "lastName": {
            "type": "string",
            "description": "The buyer's last name.",
            "example": "Doe",
            "maxLength": 50
          },
          "company": {
            "type": "string",
            "description": "The name of the buyer's company.",
            "example": "FastSpring",
            "maxLength": 255
          },
          "phoneNumber": {
            "type": "string",
            "description": "The buyer's phone number.",
            "example": "555-555-5555",
            "maxLength": 64
          }
        }
      },
      "BillToAddressResponse": {
        "type": "object",
        "description": "The physical address associated with the buyer's payment method for tax calculation and invoicing.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "ShipToAddressResponse": {
        "type": "object",
        "description": "The physical destination address to ensure accurate delivery of shipped goods.",
        "properties": {
          "addressLine1": {
            "type": "string",
            "description": "The first line of the address, typically the street number and name.",
            "maxLength": 256,
            "example": "801 Garden Street"
          },
          "addressLine2": {
            "type": "string",
            "description": "The second line of the address, typically an apartment, suite, or unit number.",
            "maxLength": 256,
            "example": "suite 201"
          },
          "city": {
            "type": "string",
            "description": "The city of the address.",
            "maxLength": 256,
            "example": "Santa Barbara"
          },
          "region": {
            "type": "string",
            "description": "Provide the state or province. Use the 2-letter state or province code for the USA and Canada. Use the full region name for other countries.",
            "maxLength": 256,
            "example": "CA"
          },
          "postalCode": {
            "type": "string",
            "description": "The postal or ZIP code.",
            "maxLength": 50,
            "example": "93101"
          }
        }
      },
      "CustomerResponse": {
        "type": "object",
        "description": "Consolidates the established customer and billing data associated with the session.",
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The unique identifier mapping the buyer to a FastSpring account record. Revealed only on authenticated requests.",
            "example": "ABC1234567890"
          },
          "externalAccountId": {
            "type": "string",
            "description": "The external ID mapping the buyer to a seller's internal platform. Revealed only on authenticated requests.",
            "example": "EXT-98765"
          },
          "savedPaymentMethod": {
            "type": "object",
            "description": "The details of a previously vaulted payment method associated with the buyer's account.",
            "properties": {
              "paymentMethodType": {
                "type": "string",
                "description": "Identifies the specific payment method brand or channel.",
                "example": "CARD"
              },
              "display": {
                "type": "string",
                "description": "A masked identifier safely describing the vaulted payment method, generally exposing only the last 4 digits.",
                "example": "VISA - 4242"
              }
            }
          },
          "billToContact": {
            "$ref": "#/components/schemas/BillToContactResponse"
          },
          "billToAddress": {
            "$ref": "#/components/schemas/BillToAddressResponse"
          },
          "shipToContact": {
            "$ref": "#/components/schemas/ShipToContactResponse"
          },
          "shipToAddress": {
            "$ref": "#/components/schemas/ShipToAddressResponse"
          },
          "accountTags": {
            "type": "object",
            "description": "A key-value map of custom tags applied to the buyer's account.",
            "example": {
              "customerType": "VIP"
            }
          },
          "taxId": {
            "type": "string",
            "description": "The buyer's active VAT, GST, or CPF identification number.",
            "example": "DE123456789"
          },
          "taxIdRegion": {
            "type": "string",
            "description": "The 2-letter state or province code associated with a US tax exemption.",
            "example": "CA"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Describes a structured error triggered by an invalid request.",
        "properties": {
          "status": {
            "type": "string",
            "description": "The HTTP status code combined with a reason phrase indicating the class of the failure.",
            "example": "400 BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "The ISO 8601 formatted timestamp marking the exact occurrence of the error.",
            "example": "2026-03-20T10:49:51.000Z"
          },
          "id": {
            "type": "string",
            "description": "A unique platform-generated identifier tracking this specific error instance for debugging.",
            "example": "FS123456789012345ABC"
          },
          "message": {
            "type": "string",
            "description": "A generalized, human-readable summary detailing the root cause of the error.",
            "example": "Invalid email format"
          },
          "errors": {
            "type": "array",
            "description": "A collection of specific validation failures mapping to discrete fields.",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A distinct string code mapping to the specific rule that failed.",
                  "example": "INVALID_EMAIL_FORMAT"
                },
                "field": {
                  "type": "string",
                  "description": "The dot-notated path explicitly pinpointing the payload property responsible for the error.",
                  "example": "customer.billToContact.email"
                },
                "message": {
                  "type": "string",
                  "description": "A specific human-readable explanation of why the mapped field failed validation.",
                  "example": "Invalid email format"
                },
                "rejectedValue": {
                  "type": "string",
                  "description": "The raw value submitted within the field that triggered the rejection.",
                  "example": "my.invalid.Email_fastspring.com"
                }
              }
            }
          }
        }
      }
    }
  },
  "security": [
    {
      "none": [
        "NONE"
      ]
    },
    {
      "basicAuth": [
        "ROLE_SELLER_API_READ_WRITE"
      ]
    },
    {
      "oauth2": [
        "ROLE_SELLER_API_READ_WRITE",
        "ROLE_SESSION_SERVICE_WRITE"
      ]
    }
  ]
}
```