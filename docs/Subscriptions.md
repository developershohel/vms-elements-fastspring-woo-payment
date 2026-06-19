Subscriptions

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Subscriptions

Each subscription instance has a unique subscription ID, which you can obtain through webhooks or API requests. The API allows you to search for subscription instances, edit subscriptions, and initiate rebills on managed subscriptions.

# Search for Subscription by Parameter

Append one or more parameters to the URL to filter search results. Use a `?` to append the first result. For subsequent search results, use `&`.

> 👍 Working with parameters
>
> Always include the event type when you use `begin` and `end` parameters, otherwise the URL variables won't filter results based on date ranges.

| Parameter   | Value                                                       |
| :---------- | :---------------------------------------------------------- |
| `accountId` | Add the account ID associated with the subscriptions.       |
| `begin`     | Specify the beginning of a date range in yyyy-mm-dd-format. |
| `end`       | Specify the end of a date range in yyyy-mm-dd format.       |

## Events

In each event, use begin and end dates to retrieve corresponding subscriptions

| Parameter      | Value                                                                        |
| :------------- | :--------------------------------------------------------------------------- |
| `canceled`     | Retrieve subscriptions that were canceled within a given date range.         |
| `charged`      | Retrieve subscription rebills from a specific date range.                    |
| `created`      | Retrieve subscriptions that were created in a specific date range.           |
| `deactivated`  | Retrieve subscriptions that were deactivated in a specific date range.       |
| `trialended`   | Retrieve subscriptions with free trials that ended in a specific date range. |
| `trialstarted` | Retrieve subscriptions with free trials that began in a specific date range. |
| `product`      | Enter product IDs to filter by specific subscriptions.                       |

## Scope

| Parameter | Value                                                                                                                |
| :-------- | :------------------------------------------------------------------------------------------------------------------- |
| all       | Unnecessary; if you do not pass a scope parameter, the API returns both live and test-mode subscriptions by default. |
| `live`    | Specify scope=live to retrieve only live (non-test-mode) subscriptions                                               |
| `test`    | Specify scope=test to retrieve only test-mode (non-live) subscriptions                                               |

## Status

| Parameter     | Value                                                                                                                                                                     |
| :------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `active`      | Specify `status=active` to retrieve only subscriptions that are currently active (note: this includes subscriptions with free trial days that are still in trial status). |
| `canceled`    | Specify `status=canceled` to retrieve only subscriptions that have been canceled but not yet deactivated.                                                                 |
| `deactivated` | Specify `status=deactivated` to retrieve only subscriptions that have been deactivated.                                                                                   |
| `overdue`     | Specify `status=overdue` to retrieve only subscriptions that are currently overdue due to a failed rebill charge.                                                         |
| trial         | Specify `status=trial` to retrieve only subscriptions that are currently in trial status (for example, through a Free Trial.                                              |

List all subscriptions

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all subscriptions

Returns a list of all subscription IDs.

> 📘 Response behavior based on parameters
>
> Detailed subscription information is returned when parameters (other than `begin` and `end`) are included in the **GET /subscriptions** request. If no parameters are applied or only begin and end are used, the response will return an array of subscription IDs.

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions": {
      "get": {
        "summary": "List all subscriptions",
        "tags": [
          "Subscriptions"
        ],
        "description": "Returns a list of all subscription IDs.",
        "operationId": "Getallsubscriptioninstances",
        "deprecated": false,
        "parameters": [
          {
            "name": "accountId",
            "in": "query",
            "required": false,
            "description": "Filter subscriptions by the associated account ID.",
            "schema": {
              "type": "string",
              "example": "abCdE1FGH2Hij3KLMnOpqR"
            }
          },
          {
            "name": "begin",
            "in": "query",
            "required": false,
            "description": "Specify the beginning of a date range in yyyy-mm-dd format.",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2025-01-01"
            }
          },
          {
            "name": "end",
            "in": "query",
            "required": false,
            "description": "Specify the end of a date range in yyyy-mm-dd format.",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2025-12-31"
            }
          },
          {
            "name": "event",
            "in": "query",
            "required": false,
            "description": "Specify the event type to filter results. Valid values include `canceled`, `charged`, `created`, `deactivated`, `trialended`, or `trialstarted`.\n\nMust be used with the `begin` and `end` parameters.\n",
            "schema": {
              "type": "string",
              "enum": [
                "canceled",
                "charged",
                "created",
                "deactivated",
                "trialended",
                "trialstarted"
              ],
              "example": "created"
            }
          },
          {
            "name": "products",
            "in": "query",
            "required": false,
            "description": "Filter subscriptions by specific product IDs.",
            "schema": {
              "type": "string",
              "example": "product-12345"
            }
          },
          {
            "name": "scope",
            "in": "query",
            "required": false,
            "description": "Specify the scope to filter by `test` or `live` subscriptions. If not provided, both live and test subscriptions are returned by default.",
            "schema": {
              "type": "string",
              "enum": [
                "all",
                "live",
                "test"
              ],
              "example": "live"
            }
          },
          {
            "name": "status",
            "in": "query",
            "required": false,
            "description": "Specify the subscription status to filter results. Valid values include `active`, `canceled`, `deactivated`, `overdue`, or `trial`.",
            "schema": {
              "type": "string",
              "enum": [
                "active",
                "canceled",
                "deactivated",
                "overdue",
                "trial"
              ],
              "example": "active"
            }
          },
          {
            "name": "page",
            "in": "query",
            "required": false,
            "description": "Specify the page number of results to retrieve. Defaults to 1 if not provided.",
            "schema": {
              "type": "integer",
              "example": 1
            }
          },
          {
            "name": "limit",
            "in": "query",
            "required": false,
            "description": "Specify the maximum number of results per page. Defaults to 50 if not provided.",
            "schema": {
              "type": "integer",
              "example": 50
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllSubscriptionsInstancesResponse"
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
      "GetAllSubscriptionsInstancesResponse": {
        "title": "Get All Subscriptions Instances Response",
        "type": "object",
        "description": "Response schema for listing all subscription instances.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscription.getall"
          },
          "result": {
            "type": "string",
            "description": "The result of the API request (e.g., success or error).",
            "example": "success"
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results. Null if no additional pages are available.",
            "example": 3
          },
          "subscriptions": {
            "type": "array",
            "description": "A list of subscription IDs returned by the API.",
            "items": {
              "type": "string",
              "description": "The unique identifier for a subscription."
            },
            "example": [
              "1abc2DE_FGhIjKLm3NoPQR",
              "a12cD2-eFGhIjKLXn4OPq",
              "12cDfgHIjKLmNOPqRs_tUVa"
            ]
          }
        }
      }
    }
  }
}
```

Update a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update a subscription

Updates active subscriptions, including product changes, discounts, add-ons, renewal settings, and more.

<details>
  <summary><b>🔍 How to use the "Try It" feature</b></summary>
  <div id="how-to-use-try-it" style={{ margin: "30px 10px" }}>
    <table>
      <thead>
        <tr>
          <th>Step</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>1. Provide basic auth credentials</strong></td>
          <td>
            <ul>
              <li>Locate the <strong>CREDENTIALS</strong> section to the right.</li>
              <li>Enter your <strong>username</strong> and <strong>password</strong> for basic authentication.</li>
              <li>Ensure the credentials are correctly input to authenticate your requests.</li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><strong>2. Choose a request example</strong></td>
          <td>
            <ul>
              <li>If multiple request examples exist, select one from the <strong>EXAMPLES</strong> drop-down menu.</li>
              <li>The request body updates automatically based on your selection.</li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><strong>3. Edit parameters</strong></td>
          <td>
            <ul>
              <li>
                <p>
                  Edit the JSON fields listed under the <strong>BODY PARAMS</strong> section. When you add a new value, the example value will be overridden. Your changes update the request preview instantly.
                </p>

```
            <Image
              align="center"
              className="border"
              border={true}
              src="https://files.readme.io/95a92216fe4a2d12708e992ad1d1bc6a9c3e25ed798e54e9eb2ac1c997a20b9c-Screenshot_2025-01-29_at_3.49.25_PM.png"
            />
          </li>
        </ul>
      </td>
    </tr>
    <tr>
      <td><strong>4. Send the request</strong></td>
      <td>
        <ul>
          <li>Click <strong>Try It!</strong> to submit the request and view the response body below.</li>
        </ul>
      </td>
    </tr>
  </tbody>
</table>
```

  </div>
</details>

<details>
  <summary><b>📘 Request body examples and use cases</b></summary>
  <div id="request-body-examples" style={{ margin: "30px 10px" }}>
    <h3>Select a request body example</h3>

```
<p>
  All of the request body examples listed below can be selected from the <strong>EXAMPLES</strong> drop-down menu in the top-right corner of the <strong>Try It!</strong> feature.
</p>

<Image
  align="center"
  className="border"
  border={true}
  src="https://files.readme.io/cf09bebcdfcb05f20a3e06592e3964056554874efaf3ce9295ab35d0dc6e5b79-Screenshot_2025-01-29_at_4.33.58_PM.png"
/>

<br />
<br />

<table border="1" cellSpacing="0" cellPadding="8">
  <thead>
    <tr>
      <th><b>Request Body Example</b></th>
      <th><b>Use Case</b></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Modify Product for Subscription</b></td>
      <td>Update the primary product, change the quantity, or apply/remove a coupon for an active subscription.</td>
    </tr>
    <tr>
      <td><b>Modify Next Charge Date</b></td>
      <td>Change the next charge date or end date of a subscription, and modify the product, quantity, or apply/remove a coupon (proration is not supported).</td>
    </tr>
    <tr>
      <td><b>Modify Price or Apply Discount</b></td>
      <td>Adjust the price of an active subscription without changing the product.</td>
    </tr>
    <tr>
      <td><b>Discount with Amount Off</b></td>
      <td>Apply a fixed amount discount to a subscription for a set duration.</td>
    </tr>
    <tr>
      <td><b>Discount with Percent Off</b></td>
      <td>Apply a percentage-based discount to a subscription for a specific number of billing periods or indefinitely.</td>
    </tr>
    <tr>
      <td><b>Preview Proration</b></td>
      <td>Preview prorated charges and credits before making actual changes to a subscription, aiding in estimating price changes due to upgrades or downgrades.</td>
    </tr>
    <tr>
      <td><b>Add or Edit Subscription Add-on</b></td>
      <td>Add a new subscription add-on or modify the quantity and pricing of an existing add-on.</td>
    </tr>
    <tr>
      <td><b>Remove a Subscription Add-on</b></td>
      <td>Remove an add-on from a subscription by setting its quantity to 0.</td>
    </tr>
    <tr>
      <td><b>Resume a Canceled Subscription</b></td>
      <td>Reactivate a subscription that was canceled but has not yet been deactivated.</td>
    </tr>
    <tr>
      <td><b>Switch to Manual Renewal</b></td>
      <td>Switch a subscription from automatic renewal to manual renewal, requiring the buyer to manually pay each period.</td>
    </tr>
    <tr>
      <td><b>Set End Date and Remaining Periods</b></td>
      <td>Set a fixed end date or define the number of remaining billing periods before a subscription stops renewing.</td>
    </tr>
    <tr>
      <td><b>Renew Subscription Indefinitely</b></td>
      <td>Remove an existing end date or remaining periods so the subscription renews indefinitely.</td>
    </tr>
    <tr>
      <td><b>Add or Update Tax Exemption ID</b></td>
      <td>Add or update a tax exemption ID for a subscription to apply tax exemption rules.</td>
    </tr>
    <tr>
      <td><b>Remove Tax Exemption ID</b></td>
      <td>Remove an existing tax exemption ID from a subscription by providing an empty string as the value.</td>
    </tr>
  </tbody>
</table>

<br />
```

  </div>
</details>

<div style={{ margin: "20px" }} />

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions": {
      "post": {
        "summary": "Update a subscription",
        "description": "Updates active subscriptions, including product changes, discounts, add-ons, renewal settings, and more.",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "updateSubscription",
        "requestBody": {
          "description": "Request body containing subscription updates.",
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "subscriptions": {
                    "type": "array",
                    "description": "List of subscription changes to apply.",
                    "items": {
                      "type": "object",
                      "required": [
                        "subscription",
                        "product",
                        "quantity"
                      ],
                      "properties": {
                        "subscription": {
                          "type": "string",
                          "description": "Unique identifier for the subscription.",
                          "example": "aBCDE12fGH3iJkL4mNOpqr"
                        },
                        "product": {
                          "type": "string",
                          "description": "Catalog product path of the new subscription product.",
                          "example": "new-monthly-subscription"
                        },
                        "quantity": {
                          "type": "integer",
                          "description": "Quantity of the product or add-on.",
                          "example": 1
                        },
                        "next": {
                          "type": "string",
                          "format": "date-time",
                          "description": "The next charge date for the subscription (UTC format or epoch time in milliseconds).",
                          "example": "2025-12-31"
                        },
                        "end": {
                          "type": "string",
                          "format": "date",
                          "description": "The end date for the subscription (YYYY-MM-DD format) or 0 for indefinite renewal.",
                          "example": "2025-12-31"
                        },
                        "isEndDateSet": {
                          "type": "boolean",
                          "description": "Indicates whether the end date is explicitly set.",
                          "example": true
                        },
                        "coupons": {
                          "type": "array",
                          "description": "Array of coupon codes applied to the subscription. Pass an empty array to remove coupons.",
                          "items": {
                            "type": "string"
                          },
                          "example": [
                            "coupon_code"
                          ]
                        },
                        "addons": {
                          "type": "array",
                          "description": "List of add-on products to add, update, or remove for the subscription.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "product": {
                                "type": "string",
                                "description": "Add-on product path.",
                                "example": "add-on-product-path"
                              },
                              "quantity": {
                                "type": "integer",
                                "description": "Quantity of the add-on product. Set to 0 to remove the add-on.",
                                "example": 0
                              },
                              "pricing": {
                                "type": "object",
                                "description": "Pricing details for the add-on.",
                                "properties": {
                                  "price": {
                                    "type": "object",
                                    "description": "Price for the add-on in various currencies.",
                                    "properties": {
                                      "USD": {
                                        "type": "number",
                                        "description": "Price in USD.",
                                        "example": 5
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        },
                        "pricing": {
                          "type": "object",
                          "description": "Pricing and discount details for the subscription.",
                          "properties": {
                            "price": {
                              "type": "object",
                              "description": "Base price for the subscription in various currencies.",
                              "properties": {
                                "USD": {
                                  "type": "number",
                                  "description": "Price in USD.",
                                  "example": 10
                                }
                              }
                            },
                            "discount": {
                              "type": "object",
                              "description": "Discount applied to the subscription.",
                              "properties": {
                                "type": {
                                  "type": "string",
                                  "description": "Type of discount (amount or percent).",
                                  "enum": [
                                    "amount",
                                    "percent"
                                  ],
                                  "example": "amount"
                                },
                                "amount": {
                                  "type": "number",
                                  "description": "Fixed discount amount.",
                                  "example": 3.5
                                },
                                "percentage": {
                                  "type": "number",
                                  "description": "Percentage discount.",
                                  "example": 5
                                },
                                "duration": {
                                  "type": "string",
                                  "description": "Duration of the discount (number of billing periods or \"all\" for indefinite).",
                                  "example": "all"
                                }
                              }
                            }
                          }
                        },
                        "manualRenew": {
                          "type": "boolean",
                          "description": "If true, changes the subscription to manual renewal.",
                          "example": true
                        },
                        "taxExemptId": {
                          "type": "string",
                          "description": "Tax exemption ID to add or update for the subscription.",
                          "example": "tax-exempt-id"
                        },
                        "deactivation": {
                          "type": "object",
                          "nullable": true,
                          "description": "Set to null to resume a canceled subscription.",
                          "example": null
                        },
                        "prorate": {
                          "type": "boolean",
                          "description": "If true, calculates prorated charges or credits based on changes made. Any refunds are applied as a credit to the remaining period.\n\nThe `prorate` parameter is not supported when updating the next charge date.\n",
                          "example": true
                        },
                        "preview": {
                          "type": "boolean",
                          "description": "If true, returns a preview of prorated charges and credits without committing the changes.",
                          "example": true
                        },
                        "remainingPeriods": {
                          "type": "integer",
                          "nullable": true,
                          "description": "Number of remaining billing periods for the subscription. Set to null for indefinite renewal.",
                          "example": 4
                        }
                      }
                    }
                  }
                },
                "required": [
                  "subscriptions"
                ]
              },
              "examples": {
                "ModifyProduct": {
                  "summary": "Modify Product for Subscription",
                  "description": "Update the primary product, quantity, and optionally apply or remove a coupon for an active subscription.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "product": "new-monthly-subscription",
                        "quantity": 1,
                        "coupons": [
                          "coupon_code"
                        ],
                        "prorate": true
                      }
                    ]
                  }
                },
                "ModifyNextChargeDate": {
                  "summary": "Modify Next Charge Date",
                  "description": "Update the next charge date and end date for a subscription. Includes options to modify product, quantity, and apply or remove a coupon. The `prorate` parameter is not supported when updating the next charge date.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "next": 1712188800000,
                        "end": "2025-12-31",
                        "product": "subscription-product-1",
                        "quantity": 1,
                        "coupons": [
                          "valid_coupon_code"
                        ],
                        "prorate": false
                      }
                    ]
                  }
                },
                "ModifyPriceOrDiscount": {
                  "summary": "Modify Price or Apply Discount",
                  "description": "Adjust the price of an active subscription without changing the product.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "pricing": {
                          "price": {
                            "USD": 10
                          }
                        }
                      }
                    ]
                  }
                },
                "DiscountWithAmountOff": {
                  "summary": "Discount with Amount Off",
                  "description": "Apply a discount of a specific amount to a subscription for a defined duration.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "pricing": {
                          "price": {
                            "USD": 10
                          },
                          "discount": {
                            "type": "amount",
                            "amount": 3.5,
                            "duration": "all"
                          }
                        }
                      }
                    ]
                  }
                },
                "DiscountWithPercentOff": {
                  "summary": "Discount with Percent Off",
                  "description": "Apply a percentage discount to a subscription for a specific number of periods or indefinitely.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "pricing": {
                          "price": 9.95,
                          "discount": {
                            "type": "percent",
                            "percentage": 5,
                            "duration": 2
                          }
                        }
                      }
                    ]
                  }
                },
                "PreviewProration": {
                  "summary": "Preview Proration",
                  "description": "Request a preview of prorated charges and credits for a subscription eligible for proration. This preview calculates the charges and credits resulting from the subscription changes without committing the changes. \n\nWhen the preview CAag is set to `true` along with the `prorate` CAag, the endpoint returns a detailed estimate of applicable charges, credits, and refunds for eligible plan modifications.\n\nProration considers the remaining balance on the subscription:\n  - For **upgrades**, the remaining balance is deducted from new charges.\n  - For **downgrades**, excess charges are refunded or credited to the buyer.\n",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "subscription-id",
                        "prorate": true,
                        "preview": true
                      }
                    ]
                  }
                },
                "AddOrEditAddon": {
                  "summary": "Add or Edit Subscription Add-on",
                  "description": "Add a new subscription add-on or update the quantity and pricing of an existing add-on.\n\nA subscription add-on product path is a standalone catalog item linked to a subscription. Add-ons renew with the subscription, and their price is treated as periodic, not one-time.\n",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "addons": [
                          {
                            "product": "add-on-product-path",
                            "quantity": 20,
                            "pricing": {
                              "price": {
                                "USD": 5
                              }
                            }
                          }
                        ],
                        "prorate": true
                      }
                    ]
                  }
                },
                "RemoveAddon": {
                  "summary": "Remove a Subscription Add-on",
                  "description": "Remove an existing add-on from a subscription by setting its quantity to 0.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "addons": [
                          {
                            "product": "existing-add-on-product-being-removed",
                            "quantity": 0
                          }
                        ],
                        "prorate": false
                      }
                    ]
                  }
                },
                "ResumeCanceledSubscription": {
                  "summary": "Resume a Canceled Subscription",
                  "description": "Resume a subscription that has been canceled but not yet deactivated.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "deactivation": null
                      }
                    ]
                  }
                },
                "SwitchToManualRenew": {
                  "summary": "Switch to Manual Renewal",
                  "description": "Change a subscription from automatic renewal to manual renewal, where payment details are not stored on file because the buyer is required to pay for each period manually.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "manualRenew": true
                      }
                    ]
                  }
                },
                "SetEndDateAndRemainingPeriods": {
                  "summary": "Set End Date and Remaining Periods",
                  "description": "Adjust a subscription's end date or define the number of remaining billing periods. This ensures the subscription stops renewing beyond the specified point.\n\nThe `end` date takes precedence if both an `end` date and `remainingPeriods` are provided.\n",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "end": "2025-12-31",
                        "isEndDateSet": true,
                        "remainingPeriods": 4
                      }
                    ]
                  }
                },
                "RenewIndefinitely": {
                  "summary": "Renew Subscription Indefinitely",
                  "description": "Reverse any previously set end dates or remaining periods to allow the subscription to renew indefinitely.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "remainingPeriods": null
                      }
                    ]
                  }
                },
                "AddOrUpdateTaxExemptionId": {
                  "summary": "Add or Update Tax Exemption ID",
                  "description": "Add or update the tax exemption ID for an active subscription to apply appropriate tax exemption rules.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "taxExemptId": "tax-exempt-id"
                      }
                    ]
                  }
                },
                "RemoveTaxExemptionId": {
                  "summary": "Remove Tax Exemption ID",
                  "description": "Remove the tax exemption ID from an active subscription by providing an empty string as the value.",
                  "value": {
                    "subscriptions": [
                      {
                        "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                        "taxExemptId": ""
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
                  "$ref": "#/components/schemas/UpdateSubscriptionResponse"
                },
                "examples": {
                  "ChangeProductResponse": {
                    "summary": "Change Product Response",
                    "description": "Response when the product for an active subscription is successfully updated.",
                    "value": {
                      "subscriptions": [
                        {
                          "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                          "action": "subscription.update",
                          "result": "success"
                        }
                      ]
                    }
                  },
                  "PreviewChargeResponse": {
                    "summary": "Preview Charge Response",
                    "description": "Response when a preview of prorated charges is requested.",
                    "value": {
                      "subscriptions": [
                        {
                          "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                          "action": "subscription.update",
                          "result": "success",
                          "prorated": true,
                          "proration": {
                            "productPath": "add-on-subscription",
                            "currency": "USD",
                            "nextPeriodStartDate": "2025-11-01T00:00:00.000Z",
                            "nextPeriodEndDate": "2025-11-30T00:00:00.000Z",
                            "previousRemainingDays": 29,
                            "periodPastDays": 1,
                            "nextRemainingDays": 29,
                            "regularPeriod": {
                              "unit": "MONTH",
                              "unitCount": 1
                            },
                            "nextPricePerPeriod": {
                              "amount": 410,
                              "period": {
                                "count": 1,
                                "unit": "month"
                              }
                            },
                            "previousPricePerPeriod": {
                              "amount": 205,
                              "period": {
                                "count": 1,
                                "unit": "month"
                              }
                            },
                            "utilizedAmount": 6.83,
                            "creditAmount": 198.17,
                            "proratedAmount": 396.34,
                            "chargeAmount": 198.17
                          }
                        }
                      ]
                    }
                  },
                  "PreviewRefundCreditResponse": {
                    "summary": "Preview Refund/Credit Response",
                    "description": "Response when a preview of prorated refunds or credits is requested.",
                    "value": {
                      "subscriptions": [
                        {
                          "subscription": "athBG1DPTFuOcw0iwPwi4A",
                          "action": "subscription.update",
                          "result": "success",
                          "prorated": true,
                          "proration": {
                            "productPath": "japan-sub",
                            "currency": "USD",
                            "nextPeriodStartDate": "2025-11-03T00:00:00.000Z",
                            "nextPeriodEndDate": "2025-11-09T00:00:00.000Z",
                            "previousRemainingDays": 30,
                            "periodPastDays": 0,
                            "nextRemainingDays": 7,
                            "regularPeriod": {
                              "unit": "WEEK",
                              "unitCount": 1
                            },
                            "nextPricePerPeriod": {
                              "amount": 10,
                              "period": {
                                "count": 1,
                                "unit": "week"
                              }
                            },
                            "previousPricePerPeriod": {
                              "amount": 100,
                              "period": {
                                "count": 1,
                                "unit": "month"
                              }
                            },
                            "utilizedAmount": 0,
                            "prorateAdjustment": 100,
                            "proratedAmount": 10,
                            "refundAmount": 90
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
      "UpdateSubscriptionResponse": {
        "title": "Update Subscription Response",
        "description": "Comprehensive schema for the response returned by the \"Update a Subscription\" endpoint.",
        "type": "object",
        "properties": {
          "subscriptions": {
            "type": "array",
            "description": "List of subscription updates.",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "The unique identifier of the subscription.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the subscription.",
                  "example": "subscription.update"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                },
                "prorated": {
                  "type": "boolean",
                  "description": "Indicates whether proration was applied to the subscription update.",
                  "example": true
                },
                "proration": {
                  "type": "object",
                  "description": "Details of proration calculations, if applicable.",
                  "properties": {
                    "productPath": {
                      "type": "string",
                      "description": "The product path for the subscription item.",
                      "example": "add-on-subscription"
                    },
                    "currency": {
                      "type": "string",
                      "description": "The currency used for the proration calculations.",
                      "example": "USD"
                    },
                    "nextPeriodStartDate": {
                      "type": "string",
                      "format": "date-time",
                      "description": "The start date of the next billing period.",
                      "example": "2025-11-01T00:00:00.000Z"
                    },
                    "nextPeriodEndDate": {
                      "type": "string",
                      "format": "date-time",
                      "description": "The end date of the next billing period.",
                      "example": "2025-11-30T00:00:00.000Z"
                    },
                    "previousRemainingDays": {
                      "type": "number",
                      "description": "The remaining days in the previous billing period before the change.",
                      "example": 29
                    },
                    "periodPastDays": {
                      "type": "number",
                      "description": "The days elapsed in the current billing period.",
                      "example": 1
                    },
                    "nextRemainingDays": {
                      "type": "number",
                      "description": "The remaining days in the new billing period after the change.",
                      "example": 29
                    },
                    "regularPeriod": {
                      "type": "object",
                      "description": "Details about the regular billing period.",
                      "properties": {
                        "unit": {
                          "type": "string",
                          "description": "Time unit for the billing period.",
                          "example": "MONTH"
                        },
                        "unitCount": {
                          "type": "integer",
                          "description": "Number of units in the billing period.",
                          "example": 1
                        }
                      }
                    },
                    "nextPricePerPeriod": {
                      "type": "object",
                      "description": "Pricing details for the next billing period.",
                      "properties": {
                        "amount": {
                          "type": "number",
                          "description": "The price amount for the next period.",
                          "example": 410
                        },
                        "period": {
                          "type": "object",
                          "description": "The period details for the price.",
                          "properties": {
                            "count": {
                              "type": "integer",
                              "description": "The number of periods.",
                              "example": 1
                            },
                            "unit": {
                              "type": "string",
                              "description": "The unit of time for the period.",
                              "example": "month"
                            }
                          }
                        }
                      }
                    },
                    "previousPricePerPeriod": {
                      "type": "object",
                      "description": "Pricing details for the previous billing period.",
                      "properties": {
                        "amount": {
                          "type": "number",
                          "description": "The price amount for the previous period.",
                          "example": 205
                        },
                        "period": {
                          "type": "object",
                          "description": "The period details for the price.",
                          "properties": {
                            "count": {
                              "type": "integer",
                              "description": "The number of periods.",
                              "example": 1
                            },
                            "unit": {
                              "type": "string",
                              "description": "The unit of time for the period.",
                              "example": "month"
                            }
                          }
                        }
                      }
                    },
                    "utilizedAmount": {
                      "type": "number",
                      "description": "The prorated amount for the used portion of the subscription.",
                      "example": 6.83
                    },
                    "creditAmount": {
                      "type": "number",
                      "description": "The credit amount applied for unused portions.",
                      "example": 198.17
                    },
                    "proratedAmount": {
                      "type": "number",
                      "description": "The prorated price for the new item.",
                      "example": 396.34
                    },
                    "chargeAmount": {
                      "type": "number",
                      "description": "The charge applied after proration.",
                      "example": 198.17
                    },
                    "prorateAdjustment": {
                      "type": "number",
                      "description": "The prorated price for the unused portion of the subscription.",
                      "example": 100
                    },
                    "refundAmount": {
                      "type": "number",
                      "description": "The refunded amount for unused portions.",
                      "example": 90
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

Preview a prorated plan change

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Preview a prorated plan change

Generates a preview of the estimated charges, credits, or refunds for a subscription before making any changes.

This endpoint returns a prorated estimate for only one subscription per request.


<Accordion title="How to use the Try It feature" icon="fa-search">
  <div class="spacer-sm" />

Use the built-in API explorer to test this endpoint directly in your browser.

  <table>
    <thead>
      <tr>
        <th><strong>Task</strong></th>
        <th><strong>Instructions</strong></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Authenticate</strong></td>
        <td>Locate the <strong>CREDENTIALS</strong> section on the right. Enter your <strong>username</strong> and <strong>password</strong> to ensure the request is authorized.</td>
      </tr>
      <tr>
        <td><strong>Select Example</strong></td>
        <td>Use the <strong>EXAMPLES</strong> drop-down menu to load a pre-configured request body. The JSON payload will update automatically.</td>
      </tr>
      <tr>
        <td><strong>Customize Data</strong></td>
        <td>Modify the JSON fields in the <strong>BODY PARAMS</strong> section. Your changes update the request preview instantly.</td>
      </tr>
      <tr>
        <td><strong>Execute</strong></td>
        <td>Click the <strong>Try It!</strong> button to send the API request and view the real-time response below.</td>
      </tr>
    </tbody>
  </table>

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Request body examples and use cases" icon="fa-book">
<div class="spacer-sm" />

#### Select a request body example

All of the request examples listed below can be selected from the **EXAMPLES** drop-down menu in the top-right corner of the **Try It!** feature.

  <Image align="center" border={false} src="https://files.readme.io/98484e9c170f3aef5edbc8df1f775aba070a71fab6135a4839f39ef2dbee7775-estimate-endpoint_request-body-examples.png" />

  <div class="spacer-md" />

  <table>
    <thead>
      <tr>
        <th><strong>Request Example</strong></th>
        <th><strong>Use Case</strong></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Estimate Subscription</strong></td>
        <td>Estimate the total cost of a subscription, including the base product, add-ons, pricing in multiple currencies, proration, and applied coupons.</td>
      </tr>
      <tr>
        <td><strong>Upgrade Plan</strong></td>
        <td>Upgrade a subscription from a lower-tier plan (e.g., "basic") to a higher-tier premium plan.</td>
      </tr>
      <tr>
        <td><strong>Downgrade Plan</strong></td>
        <td>Downgrade a subscription from a higher-tier plan (e.g., "premium") to a lower-tier basic plan.</td>
      </tr>
      <tr>
        <td><strong>Add-On Quantity Change</strong></td>
        <td>Increase the quantity of an existing add-on in a subscription.</td>
      </tr>
      <tr>
        <td><strong>Complex Subscription Change</strong></td>
        <td>Modify multiple aspects of a subscription at the same time, including:<ul><li>Updating the plan’s quantity and price</li><li>Adjusting add-on quantities and prices</li><li>Applying discounts to new or existing add-ons</li><li>Getting an updated cost estimate</li></ul></td>
      </tr>
    </tbody>
  </table>

  <div class="spacer-sm" />
</Accordion>

<Accordion title="Error handling" icon="fa-triangle-exclamation">
  <div class="spacer-sm" />

This table lists errors you may encounter when working with this endpoint and provides solutions to help you troubleshoot.

  <div class="spacer-sm" />

  <table>
    <thead>
      <tr>
        <th><strong>Status</strong></th>
        <th><strong>Error Message</strong></th>
        <th><strong>Solution</strong></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong><code>400</code></strong></td>
        <td><code>Request must contain a single root object.</code></td>
        <td>Ensure the request payload contains a single root object.</td>
      </tr>
      <tr>
        <td><strong><code>400</code></strong></td>
        <td><code>Request must contain a subscription ID.</code></td>
        <td>Ensure the <code>subscription</code> ID is included in your request payload.</td>
      </tr>
      <tr>
        <td><strong><code>400</code></strong></td>
        <td><code>Proration is not allowed for this subscription.</code></td>
        <td>Check the subscription's eligibility for proration.</td>
      </tr>
      <tr>
        <td><strong><code>400</code></strong></td>
        <td><code>Refund exceeds the last charge amount.</code></td>
        <td>Ensure the refund amount does not exceed the last charge amount.</td>
      </tr>
      <tr>
        <td><strong><code>404</code></strong></td>
        <td><code>Subscription ID does not exist: `[{subscriptionId}]`</code></td>
        <td>Verify the <code>subscription</code> ID is correct and exists.</td>
      </tr>
      <tr>
        <td><strong><code>500</code></strong></td>
        <td><code>Subscription is not changeable</code></td>
        <td>Verify that the <code>subscription</code>ID is changeable, or try again with a different subscription.</td>
      </tr>
    </tbody>
  </table>

  <div class="spacer-sm" />
</Accordion>

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/estimate": {
      "post": {
        "summary": "Preview a prorated plan change",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "SubscriptionProratePreviewEstimate",
        "description": "Generates a preview of the estimated charges, credits, or refunds for a subscription before making any changes.\n\nThis endpoint returns a prorated estimate for only one subscription per request.\n",
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "subscription",
                  "pricing"
                ],
                "properties": {
                  "subscription": {
                    "type": "string",
                    "description": "Unique identifier for the subscription.",
                    "example": "aBCDE12fGH3iJkL4mNOpqr"
                  },
                  "product": {
                    "type": "string",
                    "description": "Product identifier for the subscription change.",
                    "example": "Hulu"
                  },
                  "quantity": {
                    "type": "integer",
                    "format": "int32",
                    "description": "Quantity of the product.",
                    "example": 1
                  },
                  "pricing": {
                    "type": "object",
                    "title": "Pricing",
                    "description": "Pricing details for the subscription.",
                    "properties": {
                      "price": {
                        "type": "object",
                        "title": "Price",
                        "description": "Per-currency price map for the subscription.",
                        "properties": {
                          "USD": {
                            "type": "number",
                            "format": "double",
                            "description": "Price in USD.",
                            "example": 50
                          },
                          "EUR": {
                            "type": "number",
                            "format": "double",
                            "description": "Price in EUR.",
                            "example": 45
                          }
                        }
                      },
                      "discount": {
                        "type": "object",
                        "title": "Discount",
                        "description": "Optional discount to apply to the subscription.",
                        "properties": {
                          "type": {
                            "type": "string",
                            "description": "Discount type (e.g. 'percent' or 'amount').",
                            "example": "percent"
                          },
                          "percentage": {
                            "type": "number",
                            "format": "double",
                            "description": "Percentage discount to apply.",
                            "example": 10
                          }
                        }
                      }
                    }
                  },
                  "addons": {
                    "type": "array",
                    "description": "List of add-ons included in the subscription.",
                    "items": {
                      "type": "object",
                      "title": "AddOn",
                      "required": [
                        "pricing"
                      ],
                      "properties": {
                        "product": {
                          "type": "string",
                          "description": "Product identifier for the add-on.",
                          "example": "extra-storage"
                        },
                        "quantity": {
                          "type": "integer",
                          "format": "int32",
                          "description": "Quantity of the add-on.",
                          "example": 2
                        },
                        "pricing": {
                          "type": "object",
                          "title": "Pricing",
                          "description": "Pricing details for the add-on.",
                          "properties": {
                            "price": {
                              "type": "object",
                              "title": "Price",
                              "description": "Per-currency price map for the add-on.",
                              "properties": {
                                "USD": {
                                  "type": "number",
                                  "format": "double",
                                  "description": "Price in USD.",
                                  "example": 5
                                },
                                "EUR": {
                                  "type": "number",
                                  "format": "double",
                                  "description": "Price in EUR.",
                                  "example": 4.5
                                }
                              }
                            },
                            "discount": {
                              "type": "object",
                              "title": "Discount",
                              "description": "Optional discount to apply to the add-on.",
                              "properties": {
                                "type": {
                                  "type": "string",
                                  "description": "Discount type (e.g. 'percent' or 'amount').",
                                  "example": "percent"
                                },
                                "percentage": {
                                  "type": "number",
                                  "format": "double",
                                  "description": "Percentage discount to apply.",
                                  "example": 10
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  },
                  "prorate": {
                    "type": "boolean",
                    "description": "Indicates whether the subscription change should be prorated.",
                    "example": true
                  },
                  "coupons": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "Array of coupon codes to be applied to the subscription.\n\n> **⚠️ Note:** This field is permissive. If an invalid coupon is provided, the API **ignores it** and returns a `200 OK` (rather than a `400` error).\n>\n> You must verify the `discount` field inside the `proposedPlan` object (i.e., `proposedPlan.discount`) to confirm the coupon was successfully applied.\n",
                    "example": [
                      "20OFF"
                    ]
                  }
                }
              },
              "examples": {
                "EstimateSubscriptionExample": {
                  "summary": "Estimate Subscription",
                  "value": {
                    "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                    "product": "hulu",
                    "quantity": 1,
                    "pricing": {
                      "price": {
                        "USD": 50,
                        "EUR": 45
                      }
                    },
                    "addons": [
                      {
                        "product": "extra-storage",
                        "quantity": 2,
                        "pricing": {
                          "price": {
                            "USD": 5,
                            "EUR": 4.5
                          }
                        }
                      }
                    ],
                    "prorate": true,
                    "coupons": [
                      "20OFF"
                    ]
                  }
                },
                "PlanChangeUpgradeExample": {
                  "summary": "Upgrade Plan",
                  "description": "Upgrade a product subscription from basic to premium.",
                  "value": {
                    "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                    "product": "premium"
                  }
                },
                "PlanChangeDowngradeExample": {
                  "summary": "Downgrade Plan",
                  "description": "Downgrade a product subscription from premium to basic.",
                  "value": {
                    "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                    "product": "basic"
                  }
                },
                "AddOnQuantityChangeExample": {
                  "summary": "Add-On Quantity Change",
                  "description": "Increase the quantity of an add-on.",
                  "value": {
                    "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                    "addons": [
                      {
                        "product": "all-access-radio-podcasts-articles",
                        "quantity": 4
                      }
                    ]
                  }
                },
                "ComplexSubscriptionChangeExample": {
                  "summary": "Complex Subscription Change",
                  "description": "Modify a subscription to update the plan's quantity and price, adjust add-on quantities and prices, and apply discounts to new or existing add-ons.\n\nThis use case allows for multiple simultaneous changes to a subscription while generating an updated cost estimate.\n",
                  "value": {
                    "subscription": "aBCDE12fGH3iJkL4mNOpqr",
                    "quantity": 3,
                    "pricing": {
                      "price": {
                        "USD": 700
                      }
                    },
                    "addons": [
                      {
                        "product": "widget-enterprise-gold-addon",
                        "quantity": 5,
                        "pricing": {
                          "price": {
                            "USD": 275
                          }
                        }
                      },
                      {
                        "product": "widget-enterprise-platinum-addon",
                        "quantity": 5,
                        "pricing": {
                          "discount": {
                            "type": "percent",
                            "percentage": 10
                          }
                        }
                      }
                    ]
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
                  "$ref": "#/components/schemas/EstimateSubscriptionSuccessResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/EstimateSubscriptionErrorResponse400"
                }
              }
            }
          },
          "404": {
            "description": "Not Found",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/EstimateSubscriptionErrorResponse404"
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
      "EstimateSubscriptionSuccessResponse": {
        "title": "EstimateSubscriptionSuccessResponse",
        "type": "object",
        "properties": {
          "subscription": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "currency": {
            "type": "string",
            "description": "The subscription currency.",
            "example": "USD"
          },
          "timezone": {
            "type": "string",
            "description": "The timezone of the subscription.",
            "example": "UTC"
          },
          "periodStartDate": {
            "type": "integer",
            "format": "int64",
            "description": "Start date of the current billing period in epoch milliseconds.",
            "example": 1767744000000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Human-readable format of the start date.",
            "example": "1/7/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO 8601 format of the start date.",
            "example": "2026-01-07"
          },
          "periodEndDate": {
            "type": "integer",
            "format": "int64",
            "description": "End date of the current billing period in epoch milliseconds.",
            "example": 1770336000000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Human-readable format of the end date.",
            "example": "2/6/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO 8601 format of the end date.",
            "example": "2026-02-06"
          },
          "endDate": {
            "type": "integer",
            "format": "int64",
            "nullable": true,
            "description": "The subscription end date in epoch milliseconds. Null if auto-renewing.",
            "example": null
          },
          "endDateDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Human-readable format of the end date.",
            "example": null
          },
          "endDateDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "description": "ISO 8601 format of the end date.",
            "example": null
          },
          "remainingPeriods": {
            "type": "integer",
            "description": "Number of remaining billing periods. -1 indicates indefinite/auto-renewing.",
            "example": -1
          },
          "nextChargeDate": {
            "type": "integer",
            "format": "int64",
            "description": "The next scheduled charge date in epoch milliseconds.",
            "example": 1770422400000
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Human-readable format of the next charge date.",
            "example": "2/7/26"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "description": "ISO 8601 format of the next charge date.",
            "example": "2026-02-07"
          },
          "isProratable": {
            "type": "boolean",
            "description": "Indicates if the subscription change supports proration.",
            "example": true
          },
          "prorationStatus": {
            "type": "string",
            "description": "The status of the proration calculation.",
            "example": "Available"
          },
          "currentPlan": {
            "type": "object",
            "description": "Details of the current subscription plan.",
            "properties": {
              "display": {
                "type": "string",
                "description": "Display name of the current plan.",
                "example": "Basic"
              },
              "product": {
                "type": "string",
                "description": "Product identifier of the current plan.",
                "example": "basic"
              },
              "billingFrequency": {
                "type": "string",
                "description": "Frequency of billing for the current plan.",
                "example": "1 month"
              },
              "price": {
                "type": "number",
                "format": "float",
                "description": "Price of the current plan.",
                "example": 100
              },
              "priceDisplay": {
                "type": "string",
                "description": "Human-readable format of the price.",
                "example": "$100.00"
              },
              "discount": {
                "type": "number",
                "format": "float",
                "description": "Total discount applied to the current plan.",
                "example": 0
              },
              "discountDisplay": {
                "type": "string",
                "description": "Human-readable format of the discount.",
                "example": "$0.00"
              },
              "quantity": {
                "type": "integer",
                "description": "Quantity of the current plan.",
                "example": 1
              },
              "subtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the current plan.",
                "example": 100
              },
              "subtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subtotal.",
                "example": "$100.00"
              },
              "tax": {
                "type": "number",
                "format": "float",
                "description": "Tax amount for the current plan.",
                "example": 8
              },
              "taxDisplay": {
                "type": "string",
                "description": "Human-readable format of the tax.",
                "example": "$8.00"
              },
              "total": {
                "type": "number",
                "format": "float",
                "description": "Total amount for the current plan.",
                "example": 108
              },
              "totalDisplay": {
                "type": "string",
                "description": "Human-readable format of the total.",
                "example": "$108.00"
              },
              "taxPercent": {
                "type": "number",
                "format": "float",
                "description": "Tax percentage applied to the current plan.",
                "example": 8
              },
              "taxPercentDisplay": {
                "type": "string",
                "description": "Human-readable format of the tax percentage.",
                "example": "8%"
              },
              "periodStartDate": {
                "type": "integer",
                "format": "int64",
                "description": "Start date of the current plan period in epoch milliseconds.",
                "example": 1767744000000
              },
              "periodStartDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the start date.",
                "example": "1/7/26"
              },
              "periodStartDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the start date.",
                "example": "2026-01-07"
              },
              "periodEndDate": {
                "type": "integer",
                "format": "int64",
                "description": "End date of the current billing period in epoch milliseconds.",
                "example": 1767744000000
              },
              "periodEndDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the end date.",
                "example": "1/7/26"
              },
              "periodEndDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the end date.",
                "example": "2026-01-07"
              },
              "prorationUtilizedDays": {
                "type": "integer",
                "description": "Number of days utilized in the current period.",
                "example": 0
              },
              "prorationRemainingDays": {
                "type": "integer",
                "description": "Number of days remaining in the current period.",
                "example": 31
              },
              "prorationTotalDays": {
                "type": "integer",
                "description": "Total days in the calculation period.",
                "example": 31
              },
              "proratedItemCharge": {
                "type": "number",
                "format": "float",
                "description": "Prorated charge amount.",
                "example": 80
              },
              "proratedItemChargeDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated charge.",
                "example": "$80.00"
              },
              "proratedItemCredit": {
                "type": "number",
                "format": "float",
                "description": "Prorated credit amount.",
                "example": 100
              },
              "proratedItemCreditDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated credit.",
                "example": "$100.00"
              },
              "proratedItemSubtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the prorated items.",
                "example": -20
              },
              "proratedItemSubtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated subtotal.",
                "example": "-$20.00"
              },
              "proratedItemTax": {
                "type": "number",
                "format": "float",
                "description": "Prorated tax amount.",
                "example": 0
              },
              "proratedItemTaxDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated tax.",
                "example": "$0.00"
              },
              "proratedItemTotal": {
                "type": "number",
                "format": "float",
                "description": "Total prorated amount.",
                "example": -20
              },
              "proratedItemTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the total prorated amount.",
                "example": "-$20.00"
              },
              "addons": {
                "type": "array",
                "description": "Add-ons included in the plan.",
                "items": {
                  "type": "string",
                  "example": [
                    "all-access-radio-podcasts-articles"
                  ]
                }
              },
              "subscriptionSubtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the subscription.",
                "example": 100
              },
              "subscriptionSubtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription subtotal.",
                "example": "$100.00"
              },
              "subscriptionTax": {
                "type": "number",
                "format": "float",
                "description": "Tax amount for the subscription.",
                "example": 8
              },
              "subscriptionTaxDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription tax.",
                "example": "$8.00"
              },
              "subscriptionTotal": {
                "type": "number",
                "format": "float",
                "description": "Total amount for the subscription.",
                "example": 108
              },
              "subscriptionTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription total.",
                "example": "$108.00"
              },
              "subscriptionProratedCredit": {
                "type": "number",
                "format": "float",
                "description": "Prorated credit for the subscription.",
                "example": 100
              },
              "subscriptionProratedCreditDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated credit.",
                "example": "$100.00"
              },
              "subscriptionEndDate": {
                "type": "integer",
                "format": "int64",
                "nullable": true,
                "description": "End date of the subscription in epoch milliseconds.",
                "example": null
              },
              "subscriptionEndDateDisplay": {
                "type": "string",
                "nullable": true,
                "description": "Human-readable format of the subscription end date.",
                "example": null
              },
              "subscriptionEndDateDisplayISO8601": {
                "type": "string",
                "nullable": true,
                "description": "ISO 8601 format of the subscription end date.",
                "example": null
              }
            }
          },
          "proposedPlan": {
            "type": "object",
            "description": "Details of the proposed subscription plan.",
            "properties": {
              "display": {
                "type": "string",
                "description": "Display name of the proposed plan.",
                "example": "Basic"
              },
              "product": {
                "type": "string",
                "description": "Product identifier of the proposed plan.",
                "example": "basic"
              },
              "billingFrequency": {
                "type": "string",
                "description": "Frequency of billing for the proposed plan.",
                "example": "1 month"
              },
              "price": {
                "type": "number",
                "format": "float",
                "description": "Price of the proposed plan.",
                "example": 100
              },
              "priceDisplay": {
                "type": "string",
                "description": "Human-readable format of the price.",
                "example": "$100.00"
              },
              "discount": {
                "type": "number",
                "format": "float",
                "description": "Total discount applied to the proposed plan.",
                "example": 20
              },
              "discountDisplay": {
                "type": "string",
                "description": "Human-readable format of the discount.",
                "example": "$20.00"
              },
              "quantity": {
                "type": "integer",
                "description": "Quantity of the proposed plan.",
                "example": 1
              },
              "subtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the proposed plan.",
                "example": 80
              },
              "subtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subtotal.",
                "example": "$80.00"
              },
              "tax": {
                "type": "number",
                "format": "float",
                "description": "Tax amount for the proposed plan.",
                "example": 6.4
              },
              "taxDisplay": {
                "type": "string",
                "description": "Human-readable format of the tax.",
                "example": "$6.40"
              },
              "total": {
                "type": "number",
                "format": "float",
                "description": "Total amount for the proposed plan.",
                "example": 86.4
              },
              "totalDisplay": {
                "type": "string",
                "description": "Human-readable format of the total.",
                "example": "$86.40"
              },
              "taxPercent": {
                "type": "number",
                "format": "float",
                "description": "Tax percentage applied to the proposed plan.",
                "example": 8
              },
              "taxPercentDisplay": {
                "type": "string",
                "description": "Human-readable format of the tax percentage.",
                "example": "8%"
              },
              "periodStartDate": {
                "type": "integer",
                "format": "int64",
                "description": "Start date of the proposed plan period in epoch milliseconds.",
                "example": 1767744000000
              },
              "periodStartDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the start date.",
                "example": "1/7/26"
              },
              "periodStartDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the start date.",
                "example": "2026-01-07"
              },
              "periodEndDate": {
                "type": "integer",
                "format": "int64",
                "description": "End date of the proposed plan period in epoch milliseconds.",
                "example": 1770336000000
              },
              "periodEndDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the end date.",
                "example": "2/6/26"
              },
              "periodEndDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the end date.",
                "example": "2026-02-06"
              },
              "prorationUtilizedDays": {
                "type": "integer",
                "description": "Number of days utilized in the current period.",
                "example": 0
              },
              "prorationRemainingDays": {
                "type": "integer",
                "description": "Number of days remaining in the current period.",
                "example": 31
              },
              "prorationTotalDays": {
                "type": "integer",
                "description": "Total days in the calculation period.",
                "example": 31
              },
              "proratedItemCharge": {
                "type": "number",
                "format": "float",
                "description": "Prorated charge amount.",
                "example": 80
              },
              "proratedItemChargeDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated charge.",
                "example": "$80.00"
              },
              "proratedItemCredit": {
                "type": "number",
                "format": "float",
                "description": "Prorated credit amount.",
                "example": 100
              },
              "proratedItemCreditDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated credit.",
                "example": "$100.00"
              },
              "proratedItemSubtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the prorated items.",
                "example": -20
              },
              "proratedItemSubtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated subtotal.",
                "example": "-$20.00"
              },
              "proratedItemTax": {
                "type": "number",
                "format": "float",
                "description": "Prorated tax amount.",
                "example": 0
              },
              "proratedItemTaxDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated tax.",
                "example": "$0.00"
              },
              "proratedItemTotal": {
                "type": "number",
                "format": "float",
                "description": "Total prorated amount.",
                "example": -20
              },
              "proratedItemTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the total prorated amount.",
                "example": "-$20.00"
              },
              "addons": {
                "type": "array",
                "description": "Add-ons included in the plan.",
                "items": {
                  "type": "string",
                  "example": [
                    "all-access-radio-podcasts-articles"
                  ]
                }
              },
              "subscriptionSubtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the proposed subscription.",
                "example": 80
              },
              "subscriptionSubtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription subtotal.",
                "example": "$80.00"
              },
              "subscriptionTax": {
                "type": "number",
                "format": "float",
                "description": "Tax amount for the proposed subscription.",
                "example": 6.4
              },
              "subscriptionTaxDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription tax.",
                "example": "$6.40"
              },
              "subscriptionTotal": {
                "type": "number",
                "format": "float",
                "description": "Total amount for the proposed subscription.",
                "example": 86.4
              },
              "subscriptionTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the subscription total.",
                "example": "$86.40"
              },
              "subscriptionProratedCharge": {
                "type": "number",
                "format": "float",
                "description": "Prorated charge for the proposed subscription.",
                "example": 80
              },
              "subscriptionProratedChargeDisplay": {
                "type": "string",
                "description": "Human-readable format of the prorated charge.",
                "example": "$80.00"
              },
              "subscriptionEndDate": {
                "type": "integer",
                "format": "int64",
                "nullable": true,
                "description": "End date of the subscription in epoch milliseconds.",
                "example": null
              },
              "subscriptionEndDateDisplay": {
                "type": "string",
                "nullable": true,
                "description": "Human-readable format of the subscription end date.",
                "example": null
              },
              "subscriptionEndDateDisplayISO8601": {
                "type": "string",
                "nullable": true,
                "description": "ISO 8601 format of the subscription end date.",
                "example": null
              }
            }
          },
          "amountDue": {
            "type": "object",
            "description": "Details of the amount due for the proration.",
            "properties": {
              "prorationSubtotal": {
                "type": "number",
                "format": "float",
                "description": "Subtotal amount for the proration.",
                "example": -20
              },
              "prorationSubtotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the proration subtotal.",
                "example": "-$20.00"
              },
              "prorationTax": {
                "type": "number",
                "format": "float",
                "description": "Tax amount for the proration.",
                "example": 0
              },
              "prorationTaxDisplay": {
                "type": "string",
                "description": "Human-readable format of the proration tax.",
                "example": "$0.00"
              },
              "totalAmountDue": {
                "type": "number",
                "format": "float",
                "description": "Total amount due for the proration.",
                "example": 0
              },
              "totalAmountDueDisplay": {
                "type": "string",
                "description": "Human-readable format of the total amount due.",
                "example": "$0.00"
              },
              "nextChargeDate": {
                "type": "integer",
                "format": "int64",
                "description": "Date of the next charge in epoch milliseconds.",
                "example": 1770422400000
              },
              "nextChargeDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the next charge date.",
                "example": "2/7/26"
              },
              "nextChargeDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the next charge date.",
                "example": "2026-02-07"
              },
              "nextChargeAmount": {
                "type": "number",
                "format": "float",
                "description": "Amount of the next charge.",
                "example": 86.4
              },
              "nextChargeAmountDisplay": {
                "type": "string",
                "description": "Human-readable format of the next charge amount.",
                "example": "$86.40"
              }
            }
          },
          "discountTotals": {
            "type": "object",
            "description": "Summary of discounts applied to the estimate.",
            "properties": {
              "productLevelDiscountTotal": {
                "type": "number",
                "format": "float",
                "description": "Total discount value from product-level configurations.",
                "example": 0
              },
              "productLevelDiscountTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the product discount total.",
                "example": "$0.00"
              },
              "couponLevelDiscountTotal": {
                "type": "number",
                "format": "float",
                "description": "Total discount value from applied coupons.",
                "example": 0
              },
              "couponLevelDiscountTotalDisplay": {
                "type": "string",
                "description": "Human-readable format of the coupon discount total.",
                "example": "$0.00"
              }
            }
          }
        }
      },
      "EstimateSubscriptionErrorResponse400": {
        "title": "EstimateSubscriptionErrorResponse400",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.estimate.update"
          },
          "subscription": {
            "type": "string",
            "description": "The subscription ID relevant to the request.",
            "example": "VtKCOlk-SzSM_Zw2H5ntWw"
          },
          "result": {
            "type": "string",
            "description": "The outcome of the API request.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "message": {
                "type": "string",
                "description": "Detailed error message.",
                "example": "Request must contain a single root object. Please consult API documentation."
              }
            }
          }
        }
      },
      "EstimateSubscriptionErrorResponse404": {
        "title": "EstimateSubscriptionErrorResponse404",
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.estimate.update"
          },
          "subscription": {
            "type": "string",
            "description": "The subscription ID relevant to the request.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "result": {
            "type": "string",
            "description": "The outcome of the API request.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscription": {
                "type": "string",
                "description": "Detailed error message.",
                "example": "Subscription ID does not exist: aBCDE12fGH3iJkL4mNOpqr"
              }
            }
          }
        }
      }
    }
  }
}
```

Retrieve a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a subscription

Retrieves the details of a subscription with the given `subscription_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}": {
      "get": {
        "summary": "Retrieve a subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "Getoneormoresubscriptioninstances",
        "description": "Retrieves the details of a subscription with the given `subscription_id`.",
        "deprecated": false,
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          },
          {
            "name": "accountId",
            "in": "query",
            "description": "Filter subscriptions by the associated account ID.",
            "schema": {
              "type": "string",
              "example": "abCdE1FGH2Hij3KLMnOpqR"
            }
          },
          {
            "name": "begin",
            "in": "query",
            "description": "Specify the beginning of a date range in yyyy-mm-dd format.",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2025-01-01"
            }
          },
          {
            "name": "end",
            "in": "query",
            "description": "Specify the end of a date range in yyyy-mm-dd format.",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2025-12-31"
            }
          },
          {
            "name": "event",
            "in": "query",
            "description": "Specify the event type to filter results. Valid values include `canceled`, `charged`, `created`, `deactivated`, `trialended`, or `trialstarted`.\nMust be used with the `begin` and `end` parameters.\n",
            "schema": {
              "type": "string",
              "enum": [
                "canceled",
                "charged",
                "created",
                "deactivated",
                "trialended",
                "trialstarted"
              ],
              "example": "created"
            }
          },
          {
            "name": "products",
            "in": "query",
            "description": "Filter subscriptions by specific product IDs. Use commas to separate multiple values.",
            "schema": {
              "type": "string",
              "example": "product-12345"
            }
          },
          {
            "name": "scope",
            "in": "query",
            "description": "Specify the scope to filter by `test` or `live` subscriptions. If not provided, both live and test subscriptions are returned by default.",
            "schema": {
              "type": "string",
              "enum": [
                "all",
                "live",
                "test"
              ],
              "example": "live"
            }
          },
          {
            "name": "status",
            "in": "query",
            "description": "Specify the subscription status to filter results. Valid values include `active`, `canceled`, `deactivated`, `overdue`, or `trial`.",
            "schema": {
              "type": "string",
              "enum": [
                "active",
                "canceled",
                "deactivated",
                "overdue",
                "trial"
              ],
              "example": "active"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetASubscriptionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RetrieveSubscriptionErrorResponse400"
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
      "GetASubscriptionResponse": {
        "title": "Get A Subscription Response",
        "type": "object",
        "description": "Response schema containing detailed subscription information.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "1abc2DE_FGhIjKLm3NoPQR"
          },
          "subscription": {
            "type": "string",
            "description": "Identifier for the subscription, same as `id`.",
            "example": "1abc2DE_FGhIjKLm3NoPQR"
          },
          "active": {
            "type": "boolean",
            "description": "Indicates if the subscription is active.",
            "example": true
          },
          "state": {
            "type": "string",
            "description": "Current state of the subscription (e.g., trial, active, canceled).",
            "example": "trial"
          },
          "isSubscriptionEligibleForPauseByBuyer": {
            "type": "boolean",
            "description": "Indicates if the buyer can pause the subscription.",
            "example": false
          },
          "isPauseScheduled": {
            "type": "boolean",
            "description": "Indicates if a pause is scheduled for the subscription.",
            "example": true
          },
          "changed": {
            "type": "integer",
            "description": "Timestamp of the last change to the subscription in milliseconds.",
            "example": 1573257599032
          },
          "changedValue": {
            "type": "integer",
            "description": "Value of the last change timestamp in milliseconds.",
            "example": 1573257599032
          },
          "changedInSeconds": {
            "type": "integer",
            "description": "Value of the last change timestamp in seconds.",
            "example": 1573257599
          },
          "changedDisplay": {
            "type": "string",
            "description": "Human-readable display of the last change date.",
            "example": "11/8/19"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates if the subscription is live or in test mode.",
            "example": false
          },
          "currency": {
            "type": "string",
            "description": "Currency used for subscription billing.",
            "example": "USD"
          },
          "account": {
            "type": "string",
            "description": "Account ID associated with the subscription.",
            "example": "N8FjcSWcQNeYCc-suM1O8g"
          },
          "product": {
            "type": "string",
            "description": "Product path of the subscription.",
            "example": "example-monthly-subscription"
          },
          "sku": {
            "type": "string",
            "description": "SKU of the subscription product.",
            "example": "skusub1"
          },
          "display": {
            "type": "string",
            "description": "Display name of the subscription product.",
            "example": "Example Monthly Subscription"
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the subscription product.",
            "example": 1
          },
          "adhoc": {
            "type": "boolean",
            "description": "Indicates if the subscription is ad-hoc.",
            "example": false
          },
          "autoRenew": {
            "type": "boolean",
            "description": "Indicates if the subscription is set to auto-renew.",
            "example": true
          },
          "price": {
            "type": "number",
            "description": "Price of the subscription.",
            "example": 14.95
          },
          "priceDisplay": {
            "type": "string",
            "description": "Human-readable display of the subscription price.",
            "example": "$14.95"
          },
          "priceInPayoutCurrency": {
            "type": "number",
            "description": "Subscription price in the payout currency.",
            "example": 14.95
          },
          "priceInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Human-readable display of the subscription price in payout currency.",
            "example": "$14.95"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount applied to the subscription.",
            "example": 3.74
          },
          "discountDisplay": {
            "type": "string",
            "description": "Human-readable display of the discount amount.",
            "example": "$3.74"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "description": "Discount amount in the payout currency.",
            "example": 3.74
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Human-readable display of the discount in payout currency.",
            "example": "$3.74"
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal amount of the subscription.",
            "example": 16.21
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Human-readable display of the subscription subtotal.",
            "example": "$16.21"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "description": "Subtotal amount in the payout currency.",
            "example": 16.21
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Human-readable display of the subtotal in payout currency.",
            "example": "$16.21"
          },
          "attributes": {
            "type": "object",
            "description": "Custom attributes associated with the subscription.",
            "additionalProperties": {
              "type": "string"
            },
            "example": {
              "Furious": "Falcon",
              "CustomAttribute2": "CustomValue2"
            }
          },
          "addons": {
            "type": "array",
            "description": "Add-ons associated with the subscription.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Product path of the add-on.",
                  "example": "example-product-3"
                },
                "sku": {
                  "type": "string",
                  "description": "SKU of the add-on.",
                  "example": "skuex3"
                },
                "display": {
                  "type": "string",
                  "description": "Display name of the add-on.",
                  "example": "Example Product 3"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the add-on product.",
                  "example": 1
                },
                "price": {
                  "type": "number",
                  "description": "Price of the add-on product.",
                  "example": 5
                },
                "priceDisplay": {
                  "type": "string",
                  "description": "Human-readable display of the add-on price.",
                  "example": "$5.00"
                }
              }
            }
          },
          "nextChargeDate": {
            "type": "string",
            "format": "date-time",
            "description": "The next charge date for the subscription.",
            "example": "2025-12-01T00:00:00.000Z"
          }
        }
      },
      "RetrieveSubscriptionErrorResponse400": {
        "type": "object",
        "description": "Schema for an error response when a subscription-related action fails.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The API action performed.",
            "example": "subscription.get"
          },
          "subscription": {
            "type": "string",
            "description": "The unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "result": {
            "type": "string",
            "description": "The outcome of the API request. Will be 'error' for an error response.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details of the error.",
            "properties": {
              "subscription": {
                "type": "string",
                "description": "Error message related to the subscription.",
                "example": "Subscription not found"
              }
            }
          },
          "subscriptions": {
            "type": "array",
            "description": "An array of related subscription responses, including individual results and errors.",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The API action performed.",
                  "example": "subscription.get"
                },
                "subscription": {
                  "type": "string",
                  "description": "The unique identifier for the subscription.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "result": {
                  "type": "string",
                  "description": "The outcome of the API request.",
                  "example": "success"
                },
                "error": {
                  "type": "object",
                  "description": "Details of the error.",
                  "properties": {
                    "subscription": {
                      "type": "string",
                      "description": "Error message related to the subscription.",
                      "example": "Subscription not found"
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

Transfer subscriptions

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Transfer subscriptions

Transfers up to 20 subscriptions from a source account to a target account within the same `siteId`.

  - `sourceAccountId` and `targetAccountId` (if provided) must reference the same `siteId`.
  - A maximum of 20 subscriptions can be transferred per request.


In the “Try It!” panel, open the **EXAMPLES** dropdown to select a request example:

  | **Request example**             | **When to use it**                                                                                          |
  | ---------------------------- | ---------------------------------------------------------------------------------------------------------- |
  | New Account Transfer     | Create a new target account **and** transfer subscriptions in one call.                                     |
  | Existing Account Transfer | Transfer subscriptions to an **existing** target account.                                                  |


Transfers up to 20 subscriptions from a source account to a target account within the same `siteId`.

In the **Try It!** panel, open the **EXAMPLES** dropdown to select a request example:

| Request Example           | When to use it                                                      |
| :------------------------ | :------------------------------------------------------------------ |
| New Account Transfer      | Create a new target account and transfer subscriptions in one call. |
| Existing Account Transfer | Transfer subscriptions to an existing target account.               |

<div class="spacer"></div>

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/transfer": {
      "post": {
        "summary": "Transfer subscriptions",
        "description": "Transfers up to 20 subscriptions from a source account to a target account within the same `siteId`.\n\n  - `sourceAccountId` and `targetAccountId` (if provided) must reference the same `siteId`.\n  - A maximum of 20 subscriptions can be transferred per request.\n\n\nIn the “Try It!” panel, open the **EXAMPLES** dropdown to select a request example:\n\n  | **Request example**             | **When to use it**                                                                                          |\n  | ---------------------------- | ---------------------------------------------------------------------------------------------------------- |\n  | New Account Transfer     | Create a new target account **and** transfer subscriptions in one call.                                     |\n  | Existing Account Transfer | Transfer subscriptions to an **existing** target account.                                                  |\n",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "transferSubscription",
        "deprecated": false,
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "initiatedBy",
                  "sourceAccountId",
                  "targetAccount",
                  "subscriptions"
                ],
                "properties": {
                  "initiatedBy": {
                    "type": "string",
                    "description": "Email address of the user initiating the transfer.",
                    "example": "first.last@domain.com"
                  },
                  "sourceAccountId": {
                    "type": "string",
                    "description": "Identifier of the account that currently owns the subscriptions to transfer.",
                    "example": "aAB0cdefGHiJkL0m8n8OpQ"
                  },
                  "targetAccount": {
                    "type": "object",
                    "title": "Target Account Object",
                    "required": [
                      "email",
                      "account"
                    ],
                    "properties": {
                      "email": {
                        "type": "string",
                        "description": "Email address for the target account owner.",
                        "example": "first.last@domain.com"
                      },
                      "account": {
                        "type": "object",
                        "required": [
                          "address"
                        ],
                        "properties": {
                          "firstName": {
                            "type": "string",
                            "description": "First name of the target account owner.",
                            "example": "First"
                          },
                          "lastName": {
                            "type": "string",
                            "description": "Last name of the target account owner.",
                            "example": "Last"
                          },
                          "company": {
                            "type": "string",
                            "description": "Company name for the target account owner.",
                            "example": "Company Name"
                          },
                          "phone": {
                            "type": "string",
                            "description": "Phone number for the target account owner.",
                            "example": "555-555-5555"
                          },
                          "language": {
                            "type": "string",
                            "description": "Spoken language for the account owner (<a href=\"https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes\">two-letter language code - ISO 639</a>).",
                            "example": "en"
                          },
                          "address": {
                            "type": "object",
                            "properties": {
                              "line1": {
                                "type": "string",
                                "description": "Address line 1 (e.g., street, PO box, or company name).",
                                "example": "123 Main Street"
                              },
                              "city": {
                                "type": "string",
                                "description": "City, district, or suburb for the address.",
                                "example": "Santa Barbara"
                              },
                              "region": {
                                "type": "string",
                                "description": "State, province, or region for the address. Some validations are applied for countries like USA, Canada, and Australia.",
                                "example": "US-CA"
                              },
                              "country": {
                                "type": "string",
                                "description": "Country code for the address (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2\">two-letter country code - ISO 3166-1 alpha-2</a>).",
                                "example": "US"
                              },
                              "postalCode": {
                                "type": "string",
                                "description": "Postal or ZIP code for the address.",
                                "example": 93101
                              }
                            }
                          }
                        }
                      }
                    }
                  },
                  "subscriptions": {
                    "type": "array",
                    "description": "List of subscription IDs to be transferred.",
                    "items": {
                      "type": "string",
                      "example": [
                        "1abc2DE_FGhIjKLm3NoPQR",
                        "12cDfgHIjKLmNOPqRs_tUV"
                      ]
                    }
                  }
                }
              },
              "examples": {
                "NewAccountExample": {
                  "summary": "New Account Transfer",
                  "description": "Transfer one or more subscriptions to a new account.",
                  "value": {
                    "initiatedBy": "first.last@domain.com",
                    "sourceAccountId": "aAB0cdefGHiJkL0m8n8OpQ",
                    "targetAccount": {
                      "email": "first.last@domain.com",
                      "account": {
                        "firstName": "First",
                        "lastName": "Last",
                        "company": "Company Name",
                        "phone": "555-555-5555",
                        "language": "en",
                        "address": {
                          "line1": "123 Main Street",
                          "city": "Santa Barbara",
                          "region": "US-CA",
                          "country": "US",
                          "postalCode": 93101
                        }
                      }
                    },
                    "subscriptions": [
                      "1abc2DE_FGhIjKLm3NoPQR",
                      "12cDfgHIjKLmNOPqRs_tUV"
                    ]
                  }
                },
                "ExistingAccountExample": {
                  "summary": "Existing Account Transfer",
                  "description": "Transfer one or more subscriptions to an existing account.",
                  "value": {
                    "initiatedBy": "first.last@domain.com",
                    "sourceAccountId": "aAB0cdefGHiJkL0m8n8OpQ",
                    "targetAccountId": "rST2uv1wXy279zABCd_E2f",
                    "subscriptions": [
                      "1abc2DE_FGhIjKLm3NoPQR",
                      "12cDfgHIjKLmNOPqRs_tUV"
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
                  "$ref": "#/components/schemas/TransferSubscriptionSuccessResponse"
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
      "TransferSubscriptionSuccessResponse": {
        "title": "TransferSubscriptionSuccessResponse",
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "HTTP status code indicating the result of the transfer operation.",
            "example": "200"
          },
          "siteId": {
            "type": "string",
            "description": "Unique identifier of the site where the subscriptions reside.",
            "example": "aBCDE12fGH"
          },
          "sourceAccountId": {
            "type": "string",
            "description": "Unique identifier of the account from which subscriptions were transferred.",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "targetAccountId": {
            "type": "string",
            "description": "Unique identifier of the account to which subscriptions were transferred.",
            "example": "rST2uv1wXy279zABCd_E2f"
          },
          "transferredSubscriptions": {
            "type": "array",
            "description": "List of subscription IDs that were successfully transferred.",
            "items": {
              "type": "string"
            },
            "example": [
              "1abc2DE_FGhIjKLm3NoPQR"
            ]
          },
          "failedSubscriptions": {
            "type": "array",
            "description": "Details of any subscriptions that failed to transfer.",
            "items": {
              "type": "object",
              "properties": {
                "subscriptionId": {
                  "type": "string",
                  "description": "The ID of the subscription that could not be transferred.",
                  "example": "12cDfgHIjKLmNOPqRs_tUV"
                },
                "errorMessage": {
                  "type": "string",
                  "description": "Human-readable explanation of why the transfer failed.",
                  "example": "Subscription not found"
                },
                "errorCode": {
                  "type": "string",
                  "description": "Machine-readable code categorizing the failure reason.",
                  "example": "SUBSCRIPTION_NOT_FOUND"
                }
              }
            }
          },
          "remainingSubscriptions": {
            "type": "array",
            "description": "List of subscription IDs still associated with the source account after the transfer.",
            "items": {
              "type": "string"
            },
            "example": [
              "a12cD2-eFGhIjKLXn4OPqR"
            ]
          }
        }
      }
    }
  }
}
```

Cancel a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Cancel a subscription

Cancels an active subscription with the given `subscription_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}": {
      "delete": {
        "summary": "Cancel a subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "Cancelsubscriptioninstances",
        "description": "Cancels an active subscription with the given `subscription_id`.",
        "deprecated": false,
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          },
          {
            "name": "billingPeriod",
            "in": "query",
            "required": false,
            "description": "Defines when the subscription cancellation should occur:\n\n- **Default (End of billing period)**: `1` - Cancels the subscription at the end of the current billing period.\n- **Immediate Cancellation**: `0` - Cancels the subscription immediately.\n",
            "schema": {
              "type": "integer",
              "default": 1,
              "enum": [
                0,
                1
              ]
            },
            "example": 0
          }
        ],
        "responses": {
          "200": {
            "description": "",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CancelSubscriptionResponse"
                }
              }
            }
          },
          "500": {
            "description": "",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SubscriptionCancelErrorResponse"
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
      "CancelSubscriptionResponse": {
        "title": "CancelSubscriptionResponse",
        "description": "Response schema for successfully canceling a subscription.",
        "type": "object",
        "properties": {
          "subscriptions": {
            "type": "array",
            "description": "A list of subscription cancellation details.",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "The unique identifier of the subscription that was canceled.",
                  "example": "1abc2DE_FGhIjKLm3NoPQR"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the subscription.",
                  "example": "subscription.cancel"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the cancellation request (e.g., success or error).",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "SubscriptionCancelErrorResponse": {
        "title": "SubscriptionCancelErrorResponse",
        "description": "Response schema for errors that occur during subscription cancellation.",
        "type": "object",
        "properties": {
          "subscription": {
            "type": "string",
            "description": "The unique identifier of the subscription for which the cancellation failed.",
            "example": "subscription-id"
          },
          "action": {
            "type": "string",
            "description": "The action attempted on the subscription.",
            "example": "subscription.cancel"
          },
          "result": {
            "type": "string",
            "description": "The result of the cancellation attempt (e.g., success or error).",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error that occurred during the cancellation attempt.",
            "properties": {
              "subscription": {
                "type": "string",
                "description": "A message describing why the cancellation failed.",
                "example": "The subscription is already canceled."
              }
            }
          }
        }
      }
    }
  }
}
```

List all subscription entries

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all subscription entries

Returns an array of order objects associated with the given `subscription_id`.

Each order object provides detailed information about transactions related to the subscription, including:
- **Original Order**: The initial purchase associated with the subscription.
- **Rebills**: Recurring transactions for subscription renewals.
- **Prorations and Edits**: Transactions related to prorated adjustments or subscription changes.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}/entries": {
      "get": {
        "summary": "List all subscription entries",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "Getsubscriptioninstanceentries",
        "description": "Returns an array of order objects associated with the given `subscription_id`.\n\nEach order object provides detailed information about transactions related to the subscription, including:\n- **Original Order**: The initial purchase associated with the subscription.\n- **Rebills**: Recurring transactions for subscription renewals.\n- **Prorations and Edits**: Transactions related to prorated adjustments or subscription changes.\n",
        "deprecated": false,
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SubscriptionEntriesResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SubscriptionEntriesErrorResponse"
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
      "SubscriptionEntriesResponse": {
        "type": "array",
        "items": {
          "type": "object",
          "properties": {
            "id": {
              "type": "string",
              "description": "Unique identifier for the subscription order.",
              "example": "QT3ktTTKT1-QIAZ8QAjxFQ"
            },
            "beginEntryDate": {
              "type": "string",
              "format": "date",
              "description": "Date when the subscription entry begins.",
              "example": "2025-01-15"
            },
            "beginPeriodDate": {
              "type": "string",
              "format": "date",
              "description": "Start date of the subscription period.",
              "example": "2025-01-01"
            },
            "endPeriodDate": {
              "type": "string",
              "format": "date",
              "description": "End date of the subscription period.",
              "example": "2025-11-11"
            },
            "order": {
              "type": "object",
              "description": "Details about the order associated with the subscription.",
              "properties": {
                "order": {
                  "type": "string",
                  "description": "Unique identifier for the order.",
                  "example": "3KpFRioVTwepRifGJ-iqSg"
                },
                "id": {
                  "type": "string",
                  "description": "Another identifier for the order.",
                  "example": "3KpFRioVTwepRifGJ-iqSg"
                },
                "reference": {
                  "type": "string",
                  "description": "Order reference number.",
                  "example": "NKO250115-3434-73110B"
                },
                "buyerReference": {
                  "type": "string",
                  "nullable": true,
                  "description": "Reference for the buyer, if provided.",
                  "example": null
                },
                "ipAddress": {
                  "type": "string",
                  "format": "ipv4",
                  "description": "IP address of the buyer.",
                  "example": "47.198.84.243"
                },
                "completed": {
                  "type": "boolean",
                  "description": "Indicates if the order is completed.",
                  "example": true
                },
                "changed": {
                  "type": "integer",
                  "description": "Timestamp of the last change.",
                  "example": 1734563331876
                },
                "changedValue": {
                  "type": "integer",
                  "description": "Value of the timestamp of the last change.",
                  "example": 1734563331876
                },
                "changedInSeconds": {
                  "type": "integer",
                  "description": "Timestamp in seconds of the last change.",
                  "example": 1734563331
                },
                "changedDisplay": {
                  "type": "string",
                  "description": "Human-readable display of the change date.",
                  "example": "12/18/24"
                },
                "changedDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO 8601 format of the change date.",
                  "example": "2025-12-31"
                },
                "changedDisplayEmailEnhancements": {
                  "type": "string",
                  "description": "Email-enhanced display of the change date.",
                  "example": "Dec 31, 2025"
                },
                "changedDisplayEmailEnhancementsWithTime": {
                  "type": "string",
                  "description": "Enhanced display of the change date with time.",
                  "example": "Dec 31, 2025 11:08:51 PM"
                },
                "language": {
                  "type": "string",
                  "description": "Language code for the order.",
                  "example": "en"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the order is live.",
                  "example": false
                },
                "currency": {
                  "type": "string",
                  "description": "Currency used in the order.",
                  "example": "USD"
                },
                "payoutCurrency": {
                  "type": "string",
                  "description": "Payout currency used in the order.",
                  "example": "USD"
                },
                "quote": {
                  "type": "string",
                  "nullable": true,
                  "description": "Quote identifier for the order.",
                  "example": null
                },
                "invoiceUrl": {
                  "type": "string",
                  "format": "uri",
                  "description": "URL to the invoice of the order.",
                  "example": "https://example.test.onfastspring.com/popup-new-ui/account/order/ORDER123456/invoice/INVOICE123ABC456XYZ789"
                },
                "siteId": {
                  "type": "string",
                  "description": "Identifier for the site associated with the order.",
                  "example": "aBCDE12fGH"
                },
                "account": {
                  "type": "string",
                  "description": "Identifier for the account associated with the order.",
                  "example": "abCdE1FGH2Hij3KLMnOpqR"
                },
                "total": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount for the order.",
                  "example": 799.99
                },
                "totalDisplay": {
                  "type": "string",
                  "description": "Display of the total amount in currency format.",
                  "example": "$799.99"
                },
                "totalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount in payout currency.",
                  "example": 799.99
                },
                "totalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display of the total in payout currency format.",
                  "example": "$799.99"
                },
                "tax": {
                  "type": "number",
                  "format": "double",
                  "description": "Tax applied to the order.",
                  "example": 0
                },
                "taxDisplay": {
                  "type": "string",
                  "description": "Tax amount in currency format.",
                  "example": "$0.00"
                },
                "taxInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Tax in payout currency.",
                  "example": 0
                },
                "taxInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display of tax in payout currency format.",
                  "example": "$0.00"
                },
                "subtotal": {
                  "type": "number",
                  "format": "double",
                  "description": "Subtotal of the order before tax and discounts.",
                  "example": 799.99
                },
                "subtotalDisplay": {
                  "type": "string",
                  "description": "Subtotal in currency format.",
                  "example": "$799.99"
                },
                "subtotalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Subtotal in payout currency.",
                  "example": 799.99
                },
                "subtotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display of the subtotal in payout currency format.",
                  "example": "$799.99"
                },
                "discount": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount applied to the order.",
                  "example": 0
                },
                "discountDisplay": {
                  "type": "string",
                  "description": "Display of the discount in currency format.",
                  "example": "$0.00"
                },
                "discountInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount in payout currency.",
                  "example": 0
                },
                "discountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display of the discount in payout currency format.",
                  "example": "$0.00"
                },
                "discountWithTax": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount including tax.",
                  "example": 0
                },
                "discountWithTaxDisplay": {
                  "type": "string",
                  "description": "Display of the discount with tax in currency format.",
                  "example": "$0.00"
                },
                "discountWithTaxInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount with tax in payout currency.",
                  "example": 0
                },
                "discountWithTaxInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Display of discount with tax in payout currency.",
                  "example": "$0.00"
                },
                "billDescriptor": {
                  "type": "string",
                  "description": "Descriptor shown on billing statements.",
                  "example": "FS* fsprg.com"
                },
                "payment": {
                  "type": "object",
                  "description": "Payment details for the order.",
                  "properties": {
                    "type": {
                      "type": "string",
                      "description": "Payment type used in the order.",
                      "example": "test"
                    },
                    "cardEnding": {
                      "type": "string",
                      "description": "Last four digits of the payment card used.",
                      "example": "4242"
                    }
                  }
                },
                "customer": {
                  "type": "object",
                  "description": "Customer details associated with the order.",
                  "properties": {
                    "first": {
                      "type": "string",
                      "description": "Customer's first name.",
                      "example": "John"
                    },
                    "last": {
                      "type": "string",
                      "description": "Customer's last name.",
                      "example": "Doe"
                    },
                    "email": {
                      "type": "string",
                      "format": "email",
                      "description": "Customer's email address.",
                      "example": "dummy.email@example.com"
                    },
                    "company": {
                      "type": "string",
                      "nullable": true,
                      "description": "Company name associated with the customer, if applicable.",
                      "example": null
                    },
                    "phone": {
                      "type": "string",
                      "description": "Customer's phone number.",
                      "example": "1234567890"
                    },
                    "subscribed": {
                      "type": "boolean",
                      "description": "Whether the customer is subscribed to updates.",
                      "example": false
                    }
                  }
                },
                "address": {
                  "type": "object",
                  "description": "Billing address of the customer.",
                  "properties": {
                    "city": {
                      "type": "string",
                      "description": "City of the address.",
                      "example": "Santa Barbara"
                    },
                    "regionCode": {
                      "type": "string",
                      "description": "State or region code.",
                      "example": "CA"
                    },
                    "regionDisplay": {
                      "type": "string",
                      "description": "Display name of the state or region.",
                      "example": "California"
                    },
                    "region": {
                      "type": "string",
                      "description": "Region name.",
                      "example": "California"
                    },
                    "postalCode": {
                      "type": "string",
                      "description": "Postal or ZIP code.",
                      "example": "93101"
                    },
                    "country": {
                      "type": "string",
                      "description": "Country code in ISO 3166-1 alpha-2 format.",
                      "example": "US"
                    },
                    "display": {
                      "type": "string",
                      "description": "Display format of the complete address.",
                      "example": "Santa Barbara, California, 93101, US"
                    }
                  }
                },
                "recipients": {
                  "type": "array",
                  "description": "List of recipients associated with the order.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "recipient": {
                        "type": "object",
                        "description": "Recipient details.",
                        "properties": {
                          "first": {
                            "type": "string",
                            "description": "Recipient's first name.",
                            "example": "John"
                          },
                          "last": {
                            "type": "string",
                            "description": "Recipient's last name.",
                            "example": "Doe"
                          },
                          "email": {
                            "type": "string",
                            "format": "email",
                            "description": "Recipient's email address.",
                            "example": "recipient.email@example.com"
                          },
                          "company": {
                            "type": "string",
                            "nullable": true,
                            "description": "Company name associated with the recipient.",
                            "example": null
                          },
                          "phone": {
                            "type": "string",
                            "description": "Recipient's phone number.",
                            "example": "1234567"
                          },
                          "subscribed": {
                            "type": "boolean",
                            "description": "Whether the recipient is subscribed to updates.",
                            "example": false
                          },
                          "account": {
                            "type": "string",
                            "description": "Account identifier associated with the recipient.",
                            "example": "dummy-account-id"
                          },
                          "address": {
                            "type": "object",
                            "description": "Address details of the recipient.",
                            "properties": {
                              "city": {
                                "type": "string",
                                "description": "City of the recipient's address.",
                                "example": "Santa Barbara"
                              },
                              "regionCode": {
                                "type": "string",
                                "description": "State or region code.",
                                "example": "CA"
                              },
                              "regionDisplay": {
                                "type": "string",
                                "description": "Display name of the state or region.",
                                "example": "California"
                              },
                              "region": {
                                "type": "string",
                                "description": "Region name.",
                                "example": "California"
                              },
                              "postalCode": {
                                "type": "string",
                                "description": "Postal or ZIP code.",
                                "example": "93101"
                              },
                              "country": {
                                "type": "string",
                                "description": "Country code in ISO 3166-1 alpha-2 format.",
                                "example": "US"
                              },
                              "display": {
                                "type": "string",
                                "description": "Full formatted address.",
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
                  "description": "Notes or comments associated with the order.",
                  "items": {
                    "type": "string"
                  }
                },
                "items": {
                  "type": "array",
                  "description": "List of items in the order.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "product": {
                        "type": "string",
                        "description": "Product identifier for the item.",
                        "example": "premium-tablet"
                      },
                      "quantity": {
                        "type": "integer",
                        "description": "Quantity of the product in the order.",
                        "example": 1
                      },
                      "display": {
                        "type": "string",
                        "description": "Display name of the product.",
                        "example": "Premium Tablet"
                      },
                      "sku": {
                        "type": "string",
                        "description": "Stock Keeping Unit (SKU) of the product.",
                        "example": "sku-67890"
                      },
                      "imageUrl": {
                        "type": "string",
                        "format": "uri",
                        "nullable": true,
                        "description": "URL for the product image.",
                        "example": null
                      },
                      "shortDisplay": {
                        "type": "string",
                        "description": "Shortened display name of the product.",
                        "example": "Tablet"
                      },
                      "subtotal": {
                        "type": "number",
                        "format": "double",
                        "description": "Subtotal price for the product.",
                        "example": 799.99
                      },
                      "subtotalDisplay": {
                        "type": "string",
                        "description": "Subtotal price displayed in currency format.",
                        "example": "$799.99"
                      },
                      "subtotalInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Subtotal in payout currency.",
                        "example": 799.99
                      },
                      "subtotalInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Subtotal in payout currency format.",
                        "example": "$799.99"
                      },
                      "attributes": {
                        "type": "object",
                        "description": "Additional attributes of the product.",
                        "properties": {
                          "color": {
                            "type": "string",
                            "description": "Color of the product.",
                            "example": "Black"
                          },
                          "storage": {
                            "type": "string",
                            "description": "Storage capacity of the product.",
                            "example": "128GB"
                          }
                        }
                      },
                      "discount": {
                        "type": "number",
                        "format": "double",
                        "description": "Discount applied to the item.",
                        "example": 0
                      },
                      "discountDisplay": {
                        "type": "string",
                        "description": "Display format of the discount.",
                        "example": "$0.00"
                      },
                      "discountInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Discount amount in payout currency.",
                        "example": 0
                      },
                      "discountInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display of the discount in payout currency format.",
                        "example": "$0.00"
                      },
                      "isSubscription": {
                        "type": "boolean",
                        "description": "Indicates if the item is a subscription.",
                        "example": true
                      },
                      "subscription": {
                        "type": "string",
                        "description": "Subscription identifier associated with the item.",
                        "example": "aBCDE12fGH3iJkL4mNOpqr"
                      },
                      "fulfillments": {
                        "type": "object",
                        "description": "Fulfillment details for the item.",
                        "properties": {
                          "instructions": {
                            "type": "string",
                            "description": "Fulfillment instructions for the item.",
                            "example": "<p>Expected delivery within 5 business days.</p>"
                          }
                        }
                      },
                      "withholdings": {
                        "type": "object",
                        "description": "Withholding details for the item.",
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
                        "format": "double",
                        "description": "Prorated change amount for the item.",
                        "example": 0
                      },
                      "proratedItemChangeAmountDisplay": {
                        "type": "string",
                        "description": "Prorated change amount display format.",
                        "example": "$0.00"
                      },
                      "proratedItemChangeAmountInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Prorated change amount in payout currency.",
                        "example": 0
                      },
                      "proratedItemChangeAmountInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of the prorated change amount in payout currency.",
                        "example": "$0.00"
                      },
                      "proratedItemProratedCharge": {
                        "type": "number",
                        "format": "double",
                        "description": "Total prorated charge for the item.",
                        "example": 0
                      },
                      "proratedItemProratedChargeDisplay": {
                        "type": "string",
                        "description": "Prorated charge display format.",
                        "example": "$0.00"
                      },
                      "proratedItemProratedChargeInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Prorated charge in payout currency.",
                        "example": 0
                      },
                      "proratedItemProratedChargeInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of prorated charge in payout currency.",
                        "example": "$0.00"
                      },
                      "proratedItemCreditAmount": {
                        "type": "number",
                        "format": "double",
                        "description": "Total credit amount for the item.",
                        "example": 0
                      },
                      "proratedItemCreditAmountDisplay": {
                        "type": "string",
                        "description": "Display format of the total credit amount.",
                        "example": "$0.00"
                      },
                      "proratedItemCreditAmountInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Credit amount in payout currency.",
                        "example": 0
                      },
                      "proratedItemCreditAmountInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of the credit amount in payout currency.",
                        "example": "$0.00"
                      },
                      "proratedItemTaxAmount": {
                        "type": "number",
                        "format": "double",
                        "description": "Tax amount for the prorated item.",
                        "example": 0
                      },
                      "proratedItemTaxAmountDisplay": {
                        "type": "string",
                        "description": "Tax amount display format.",
                        "example": "$0.00"
                      },
                      "proratedItemTaxAmountInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Tax amount in payout currency.",
                        "example": 0
                      },
                      "proratedItemTaxAmountInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of tax amount in payout currency.",
                        "example": "$0.00"
                      },
                      "proratedItemTotal": {
                        "type": "number",
                        "format": "double",
                        "description": "Total prorated amount for the item.",
                        "example": 0
                      },
                      "proratedItemTotalDisplay": {
                        "type": "string",
                        "description": "Total prorated amount display format.",
                        "example": "$0.00"
                      },
                      "proratedItemTotalInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Total prorated amount in payout currency.",
                        "example": 0
                      },
                      "proratedItemTotalInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of the prorated total in payout currency.",
                        "example": "$0.00"
                      }
                    }
                  }
                },
                "returns": {
                  "type": "array",
                  "description": "List of returns associated with the order.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "return": {
                        "type": "string",
                        "description": "Return identifier for the order.",
                        "example": "AbC1D2eFGH34ijklmnopQrs"
                      },
                      "amount": {
                        "type": "number",
                        "format": "double",
                        "description": "Amount returned.",
                        "example": 799.99
                      },
                      "amountDisplay": {
                        "type": "string",
                        "description": "Amount returned displayed in currency format.",
                        "example": "$799.99"
                      },
                      "amountInPayoutCurrency": {
                        "type": "number",
                        "format": "double",
                        "description": "Amount returned in payout currency.",
                        "example": 799.99
                      },
                      "amountInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Display format of the amount in payout currency.",
                        "example": "$799.99"
                      }
                    }
                  }
                }
              }
            }
          }
        }
      },
      "SubscriptionEntriesErrorResponse": {
        "type": "object",
        "properties": {
          "message": {
            "type": "string",
            "description": "Descriptive error message explaining the issue.",
            "example": "not found"
          },
          "params": {
            "type": "array",
            "description": "List of parameters related to the error. Typically empty in this scenario.",
            "items": {
              "type": "string",
              "example": null
            }
          }
        }
      }
    }
  }
}
```

Rebill a managed subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Rebill a managed subscription

Charges a customer for their [managed subscription](https://developer.fastspring.com/docs/set-up-a-managed-subscription).

This API processes charges immediately and sends notifications to the customer. It is used for managed subscriptions that rely on seller actions for charges, reminders, and deactivation, with no internal subscription date tracking.

For large rebill volumes (e.g., January 1st annual renewals), we recommend to divide workloads into batches and call the endpoint sequentially, waiting for each batch to complete.Partial failures in a batch are detailed in the response for each subscription.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/charge": {
      "post": {
        "summary": "Rebill a managed subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "Rebillmanagedsubscriptioninstance",
        "description": "Charges a customer for their [managed subscription](https://developer.fastspring.com/docs/set-up-a-managed-subscription).\n\nThis API processes charges immediately and sends notifications to the customer. It is used for managed subscriptions that rely on seller actions for charges, reminders, and deactivation, with no internal subscription date tracking.\n\nFor large rebill volumes (e.g., January 1st annual renewals), we recommend to divide workloads into batches and call the endpoint sequentially, waiting for each batch to complete.Partial failures in a batch are detailed in the response for each subscription.\n",
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/ChargeSubscriptionRequest"
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
                  "$ref": "#/components/schemas/ChargeSubscriptionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ChargeSubscriptionErrorResponse"
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
      "ChargeSubscriptionRequest": {
        "type": "object",
        "title": "ChargeSubscriptionRequest",
        "description": "Request schema for charging a subscription.",
        "properties": {
          "subscriptions": {
            "type": "array",
            "description": "List of subscriptions to charge.",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "Unique identifier of the subscription to charge. We recommended a max of 50 subscription IDs in a request body batch.",
                  "example": "subscription-id"
                }
              }
            }
          }
        }
      },
      "ChargeSubscriptionResponse": {
        "type": "object",
        "title": "ChargeSubscriptionResponse",
        "description": "Response schema for a successful subscription charge.",
        "properties": {
          "subscription": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "Identifier of the charged subscription.",
                  "example": "subscription-id"
                },
                "action": {
                  "type": "string",
                  "description": "Action performed (e.g., subscription.charge).",
                  "example": "subscription.charge"
                },
                "result": {
                  "type": "string",
                  "description": "Result of the charge operation.",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "ChargeSubscriptionErrorResponse": {
        "type": "object",
        "title": "SubscriptionChargeErrorResponse",
        "description": "Error response schema for an unsuccessful subscription charge.",
        "properties": {
          "subscriptions": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "Unique identifier of the subscription that failed to charge.",
                  "example": "subscription-id"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the subscription.",
                  "example": "subscription.charge"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the subscription charge attempt.",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "properties": {
                    "subscription": {
                      "type": "string",
                      "description": "Description of the error encountered during the subscription charge attempt.",
                      "example": "Renew can only be performed for 'Adhoc' subscriptions."
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

Pause a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Pause a subscription

Pauses a subscription with the given `subscription_id`.

The subscription pauses on the next billing cycle date and transitions to a "paused" status instead of charging the customer.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}/pause": {
      "post": {
        "summary": "Pause a subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "Pauseasubscription",
        "description": "Pauses a subscription with the given `subscription_id`.\n\nThe subscription pauses on the next billing cycle date and transitions to a \"paused\" status instead of charging the customer.\n",
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          }
        ],
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/PauseSubscriptionRequest"
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
                  "$ref": "#/components/schemas/PauseSubscriptionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PauseSubscriptionErrorResponse"
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
      "PauseSubscriptionRequest": {
        "type": "object",
        "title": "PauseSubscriptionRequest",
        "properties": {
          "pausePeriodCount": {
            "type": "integer",
            "description": "Number of periods the subscription should be paused.",
            "example": 2
          }
        },
        "required": [
          "pausePeriodCount"
        ],
        "example": {
          "pausePeriodCount": 2
        }
      },
      "PauseSubscriptionResponse": {
        "type": "object",
        "description": "Detailed subscription information.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "quote": {
            "type": "string",
            "nullable": true,
            "description": "Quote associated with the subscription, if any.",
            "example": null
          },
          "subscription": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "active": {
            "type": "boolean",
            "description": "Indicates if the subscription is active.",
            "example": true
          },
          "state": {
            "type": "string",
            "description": "Current state of the subscription (e.g., active, paused).",
            "example": "active"
          },
          "isSubscriptionEligibleForPauseByBuyer": {
            "type": "boolean",
            "description": "Indicates if the subscription can be paused by the buyer.",
            "example": false
          },
          "isPauseScheduled": {
            "type": "boolean",
            "description": "Indicates if a pause is scheduled for the subscription.",
            "example": true
          },
          "changed": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the last change in milliseconds.",
            "example": 1737753214722
          },
          "changedValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the last change in milliseconds.",
            "example": 1737753214722
          },
          "changedInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Timestamp of the last change in seconds.",
            "example": 1737753214
          },
          "changedDisplay": {
            "type": "string",
            "description": "Formatted display of the last change date.",
            "example": "1/24/25"
          },
          "changedDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted last change date.",
            "example": "2025-01-24"
          },
          "changedDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted last change date.",
            "example": "Jan 01, 2025"
          },
          "changedDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted last change date with time.",
            "example": "Jan 01, 2025 09:13:34 PM"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates if the subscription is live or in test mode.",
            "example": false
          },
          "currency": {
            "type": "string",
            "description": "Currency code of the subscription.",
            "example": "USD"
          },
          "account": {
            "type": "string",
            "description": "Identifier for the account associated with the subscription.",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "product": {
            "type": "string",
            "description": "Identifier for the subscription product.",
            "example": "furious-falcon-annual-subscription"
          },
          "sku": {
            "type": "string",
            "nullable": true,
            "description": "SKU of the subscription product, if available.",
            "example": null
          },
          "display": {
            "type": "string",
            "description": "Display name of the subscription product.",
            "example": "Furious Falcon Annual Subscription"
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the subscription product.",
            "example": 1
          },
          "adhoc": {
            "type": "boolean",
            "description": "Indicates if the subscription is ad hoc.",
            "example": false
          },
          "autoRenew": {
            "type": "boolean",
            "description": "Indicates if the subscription auto-renews.",
            "example": true
          },
          "price": {
            "type": "number",
            "format": "double",
            "description": "Price of the subscription.",
            "example": 10
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted price of the subscription.",
            "example": "$10.00"
          },
          "priceInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Price in the payout currency.",
            "example": 10
          },
          "priceInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted price in the payout currency.",
            "example": "$10.00"
          },
          "discount": {
            "type": "number",
            "format": "double",
            "description": "Discount applied to the subscription.",
            "example": 0
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount.",
            "example": "$0.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Discount in the payout currency.",
            "example": 0
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted discount in the payout currency.",
            "example": "$0.00"
          },
          "subtotal": {
            "type": "number",
            "format": "double",
            "description": "Subtotal of the subscription.",
            "example": 10
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$10.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Subtotal in the payout currency.",
            "example": 10
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted subtotal in the payout currency.",
            "example": "$10.00"
          },
          "next": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next billing date (in milliseconds).",
            "example": 1825977600000
          },
          "nextValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next billing date in milliseconds.",
            "example": 1825977600000
          },
          "nextInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Timestamp for the next billing date in seconds.",
            "example": 1825977600
          },
          "nextDisplay": {
            "type": "string",
            "description": "Formatted display of the next billing date.",
            "example": "11/12/27"
          },
          "nextDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next billing date.",
            "example": "2027-11-12"
          },
          "end": {
            "type": "string",
            "nullable": true,
            "description": "End date of the subscription, if applicable.",
            "example": null
          },
          "endValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the end date of the subscription, if applicable.",
            "example": null
          },
          "endInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "End date of the subscription in seconds.",
            "example": null
          },
          "endDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the end date of the subscription.",
            "example": null
          },
          "endDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted end date of the subscription.",
            "example": null
          },
          "canceledDate": {
            "type": "string",
            "nullable": true,
            "description": "Date the subscription was canceled, if applicable.",
            "example": null
          },
          "canceledDateValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the canceled date of the subscription.",
            "example": null
          },
          "canceledDateInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "Timestamp of the canceled date in seconds.",
            "example": null
          },
          "canceledDateDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the canceled date.",
            "example": null
          },
          "canceledDateDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted canceled date.",
            "example": null
          },
          "deactivationDate": {
            "type": "string",
            "nullable": true,
            "description": "Date the subscription was deactivated, if applicable.",
            "example": null
          },
          "deactivationDateValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the deactivation date of the subscription.",
            "example": null
          },
          "deactivationDateInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "Deactivation date of the subscription in seconds.",
            "example": null
          },
          "deactivationDateDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the deactivation date.",
            "example": null
          },
          "deactivationDateDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted deactivation date.",
            "example": null
          },
          "sequence": {
            "type": "integer",
            "description": "Sequence number of the subscription's billing cycle.",
            "example": 1
          },
          "periods": {
            "type": "string",
            "nullable": true,
            "description": "Total number of billing periods, if applicable.",
            "example": null
          },
          "remainingPeriods": {
            "type": "string",
            "nullable": true,
            "description": "Remaining billing periods, if applicable.",
            "example": null
          },
          "begin": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the subscription start date in milliseconds.",
            "example": 1731383443568
          },
          "beginValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the subscription start date in milliseconds.",
            "example": 1731383443568
          },
          "beginInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Subscription start date in seconds.",
            "example": 1731383443
          },
          "beginDisplay": {
            "type": "string",
            "description": "Formatted display of the subscription start date.",
            "example": "11/12/24"
          },
          "beginDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted subscription start date.",
            "example": "2025-01-01"
          },
          "beginDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted subscription start date.",
            "example": "Jan 01, 2025"
          },
          "beginDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted subscription start date with time.",
            "example": "Jan 01, 2025 03:50:43 AM"
          },
          "nextDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted next billing date.",
            "example": "Jan 01, 2027"
          },
          "nextDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted next billing date with time.",
            "example": "Jan 01, 2027 12:00:00 AM"
          },
          "intervalUnit": {
            "type": "string",
            "description": "Unit of the billing interval (e.g., year, month).",
            "example": "year"
          },
          "intervalUnitAbbreviation": {
            "type": "string",
            "description": "Abbreviation for the billing interval unit.",
            "example": "yr"
          },
          "intervalLength": {
            "type": "integer",
            "description": "Length of the billing interval.",
            "example": 1
          },
          "intervalLengthGtOne": {
            "type": "boolean",
            "description": "Indicates if the interval length is greater than one.",
            "example": false
          },
          "nextChargeCurrency": {
            "type": "string",
            "description": "Currency for the next charge.",
            "example": "USD"
          },
          "nextChargeDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next charge date in milliseconds.",
            "example": 1825977600000
          },
          "nextChargeDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next charge date in milliseconds.",
            "example": 1825977600000
          },
          "nextChargeDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Next charge date in seconds.",
            "example": 1825977600
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Formatted display of the next charge date.",
            "example": "11/12/27"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next charge date.",
            "example": "2027-11-12"
          },
          "nextChargePreTax": {
            "type": "number",
            "format": "double",
            "description": "Pre-tax amount of the next charge.",
            "example": 10
          },
          "nextChargePreTaxDisplay": {
            "type": "string",
            "description": "Formatted pre-tax amount of the next charge.",
            "example": "$10.00"
          },
          "nextChargePreTaxInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Pre-tax amount of the next charge in the payout currency.",
            "example": 10
          },
          "nextChargePreTaxInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted pre-tax amount in the payout currency.",
            "example": "$10.00"
          },
          "nextChargeTotal": {
            "type": "number",
            "format": "double",
            "description": "Total amount of the next charge.",
            "example": 10
          },
          "nextChargeTotalDisplay": {
            "type": "string",
            "description": "Formatted total amount of the next charge.",
            "example": "$10.00"
          },
          "nextChargeTotalInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Total amount in the payout currency.",
            "example": 10
          },
          "nextChargeTotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted total amount in the payout currency.",
            "example": "$10.00"
          },
          "nextNotificationType": {
            "type": "string",
            "description": "Type of the next notification (e.g., PAYMENT_REMINDER).",
            "example": "PAYMENT_REMINDER"
          },
          "nextNotificationDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next notification date in milliseconds.",
            "example": 1825372800000
          },
          "nextNotificationDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next notification date in milliseconds.",
            "example": 1825372800000
          },
          "nextNotificationDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Next notification date in seconds.",
            "example": 1825372800
          },
          "nextNotificationDateDisplay": {
            "type": "string",
            "description": "Formatted display of the next notification date.",
            "example": "11/5/27"
          },
          "nextNotificationDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next notification date.",
            "example": "2027-11-05"
          },
          "paymentReminder": {
            "type": "object",
            "description": "Details about the payment reminder interval.",
            "properties": {
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the payment reminder interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the payment reminder interval.",
                "example": 1
              }
            }
          },
          "paymentOverdue": {
            "type": "object",
            "description": "Details about overdue payment settings.",
            "properties": {
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the overdue payment interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the overdue payment interval.",
                "example": 1
              },
              "total": {
                "type": "integer",
                "description": "Total number of overdue payment intervals allowed.",
                "example": 4
              },
              "sent": {
                "type": "integer",
                "description": "Number of overdue payment notifications already sent.",
                "example": 0
              }
            }
          },
          "cancellationSetting": {
            "type": "object",
            "description": "Settings related to subscription cancellation.",
            "properties": {
              "cancellation": {
                "type": "string",
                "description": "Type of cancellation setting (e.g., AFTER_LAST_NOTIFICATION).",
                "example": "AFTER_LAST_NOTIFICATION"
              },
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the cancellation interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the cancellation interval.",
                "example": 1
              }
            }
          },
          "pauseDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the pause date in milliseconds.",
            "example": 1762905600000
          },
          "pauseDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the pause date in milliseconds.",
            "example": 1762905600000
          },
          "pauseDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Pause date in seconds.",
            "example": 1762905600
          },
          "pauseDateDisplay": {
            "type": "string",
            "description": "Formatted display of the pause date.",
            "example": "11/12/25"
          },
          "pauseDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted pause date.",
            "example": "2025-11-12"
          },
          "resumeDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the resume date in milliseconds.",
            "example": 1825977600000
          },
          "resumeDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the resume date in milliseconds.",
            "example": 1825977600000
          },
          "resumeDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Resume date in seconds.",
            "example": 1825977600
          },
          "resumeDateDisplay": {
            "type": "string",
            "description": "Formatted display of the resume date.",
            "example": "11/12/27"
          },
          "resumeDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted resume date.",
            "example": "2027-11-12"
          },
          "fulfillments": {
            "type": "object",
            "description": "Fulfillment details for the subscription.",
            "additionalProperties": true
          },
          "instructions": {
            "type": "array",
            "description": "Array of instruction objects providing details about subscription periods.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Identifier for the product associated with the instruction.",
                  "example": "furious-falcon-annual-subscription"
                },
                "type": {
                  "type": "string",
                  "description": "Type of the instruction (e.g., regular, discounted).",
                  "example": "regular"
                },
                "isNotTrial": {
                  "type": "boolean",
                  "description": "Indicates whether the instruction is not for a trial period.",
                  "example": true
                },
                "periodStartDate": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Timestamp of the period start date in milliseconds.",
                  "example": 1731369600000
                },
                "periodStartDateValue": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Value of the period start date in milliseconds.",
                  "example": 1731369600000
                },
                "periodStartDateInSeconds": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Period start date in seconds.",
                  "example": 1731369600
                },
                "periodStartDateDisplay": {
                  "type": "string",
                  "description": "Formatted display of the period start date.",
                  "example": "11/12/24"
                },
                "periodStartDateDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO 8601 formatted period start date.",
                  "example": "2025-01-01"
                },
                "periodEndDate": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Timestamp of the period end date in milliseconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateValue": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Value of the period end date in milliseconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateInSeconds": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Period end date in seconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateDisplay": {
                  "type": "string",
                  "description": "Formatted display of the period end date. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO 8601 formatted period end date. Can be null if not applicable.",
                  "example": null
                },
                "intervalUnit": {
                  "type": "string",
                  "description": "Unit of the subscription interval (e.g., year, month).",
                  "example": "year"
                },
                "intervalLength": {
                  "type": "integer",
                  "description": "Length of the subscription interval.",
                  "example": 1
                },
                "discountPercent": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount percentage applied during the period.",
                  "example": 0
                },
                "discountPercentValue": {
                  "type": "number",
                  "format": "double",
                  "description": "Value of the discount percentage.",
                  "example": 0
                },
                "discountPercentDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount percentage.",
                  "example": "0%"
                },
                "discountTotal": {
                  "type": "number",
                  "format": "double",
                  "description": "Total discount amount during the period.",
                  "example": 0
                },
                "discountTotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total discount amount.",
                  "example": "$0.00"
                },
                "discountTotalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total discount amount in payout currency during the period.",
                  "example": 0
                },
                "discountTotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total discount amount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount per unit during the period.",
                  "example": 0
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$0.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount per unit in payout currency during the period.",
                  "example": 0
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit in payout currency.",
                  "example": "$0.00"
                },
                "price": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit during the period.",
                  "example": 10
                },
                "priceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit.",
                  "example": "$10.00"
                },
                "priceInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit in payout currency during the period.",
                  "example": 10
                },
                "priceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit in payout currency.",
                  "example": "$10.00"
                },
                "priceTotal": {
                  "type": "number",
                  "format": "double",
                  "description": "Total price during the period.",
                  "example": 10
                },
                "priceTotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total price.",
                  "example": "$10.00"
                },
                "priceTotalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total price in payout currency during the period.",
                  "example": 10
                },
                "priceTotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total price in payout currency.",
                  "example": "$10.00"
                },
                "unitPrice": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit during the period.",
                  "example": 10
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit.",
                  "example": "$10.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit in payout currency during the period.",
                  "example": 10
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit in payout currency.",
                  "example": "$10.00"
                },
                "total": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount for the period.",
                  "example": 10
                },
                "totalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount.",
                  "example": "$10.00"
                },
                "totalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount in payout currency during the period.",
                  "example": 10
                },
                "totalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount in payout currency.",
                  "example": "$10.00"
                },
                "totalWithTaxes": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount including taxes for the period.",
                  "example": 10
                },
                "totalWithTaxesDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount including taxes.",
                  "example": "$10.00"
                },
                "totalWithTaxesInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount including taxes in payout currency during the period.",
                  "example": 10
                },
                "totalWithTaxesInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount including taxes in payout currency.",
                  "example": "$10.00"
                }
              }
            }
          },
          "initialOrderId": {
            "type": "string",
            "description": "Unique identifier for the initial order associated with the subscription.",
            "example": "9_fopjeBRBuCgCz0Nm2VXw"
          },
          "initialOrderReference": {
            "type": "string",
            "description": "Reference number for the initial order associated with the subscription.",
            "example": "NKO241112-7593-98143"
          },
          "action": {
            "type": "string",
            "description": "Action performed on the subscription.",
            "example": "subscriptions.pause.get"
          },
          "result": {
            "type": "string",
            "description": "Result of the action performed, indicating success or failure.",
            "example": "success"
          }
        }
      },
      "PauseSubscriptionErrorResponse": {
        "type": "object",
        "title": "PauseSubscriptionErrorResponse",
        "description": "Error response schema when pausing a subscription fails.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action that was attempted.",
            "example": "subscriptions.pause"
          },
          "result": {
            "type": "string",
            "description": "The result of the action, indicating an error.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error that occurred.",
            "properties": {
              "subscriptions.pause": {
                "type": "string",
                "description": "Error message specific to the pause subscription action.",
                "example": "Number of periods is required."
              }
            }
          }
        }
      }
    }
  }
}
```

Resume a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Resume a subscription

Resumes a subscription that has been scheduled for a pause.

If you cancel an upcoming pause, FastSpring will immediately charge the customer and start a new billing cycle.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}/resume": {
      "post": {
        "summary": "Resume a subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "resumeapausedsubscription",
        "description": "Resumes a subscription that has been scheduled for a pause.\n\nIf you cancel an upcoming pause, FastSpring will immediately charge the customer and start a new billing cycle.\n",
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          }
        ],
        "deprecated": false,
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ResumeSubscriptionResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ResumeSubscriptionErrorResponse"
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
      "ResumeSubscriptionResponse": {
        "type": "object",
        "description": "Detailed subscription information.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "quote": {
            "type": "string",
            "nullable": true,
            "description": "Quote associated with the subscription, if any.",
            "example": null
          },
          "subscription": {
            "type": "string",
            "description": "Unique identifier for the subscription.",
            "example": "aBCDE12fGH3iJkL4mNOpqr"
          },
          "active": {
            "type": "boolean",
            "description": "Indicates if the subscription is active.",
            "example": true
          },
          "state": {
            "type": "string",
            "description": "Current state of the subscription (e.g., active, paused).",
            "example": "active"
          },
          "isSubscriptionEligibleForPauseByBuyer": {
            "type": "boolean",
            "description": "Indicates if the subscription can be paused by the buyer.",
            "example": false
          },
          "isPauseScheduled": {
            "type": "boolean",
            "description": "Indicates if a pause is scheduled for the subscription.",
            "example": true
          },
          "changed": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the last change in milliseconds.",
            "example": 1737753214722
          },
          "changedValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the last change in milliseconds.",
            "example": 1737753214722
          },
          "changedInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Timestamp of the last change in seconds.",
            "example": 1737753214
          },
          "changedDisplay": {
            "type": "string",
            "description": "Formatted display of the last change date.",
            "example": "1/24/25"
          },
          "changedDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted last change date.",
            "example": "2025-01-24"
          },
          "changedDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted last change date.",
            "example": "Jan 01, 2025"
          },
          "changedDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted last change date with time.",
            "example": "Jan 01, 2025 09:13:34 PM"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates if the subscription is live or in test mode.",
            "example": false
          },
          "currency": {
            "type": "string",
            "description": "Currency code of the subscription.",
            "example": "USD"
          },
          "account": {
            "type": "string",
            "description": "Identifier for the account associated with the subscription.",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "product": {
            "type": "string",
            "description": "Identifier for the subscription product.",
            "example": "furious-falcon-annual-subscription"
          },
          "sku": {
            "type": "string",
            "nullable": true,
            "description": "SKU of the subscription product, if available.",
            "example": null
          },
          "display": {
            "type": "string",
            "description": "Display name of the subscription product.",
            "example": "Furious Falcon Annual Subscription"
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the subscription product.",
            "example": 1
          },
          "adhoc": {
            "type": "boolean",
            "description": "Indicates if the subscription is ad hoc.",
            "example": false
          },
          "autoRenew": {
            "type": "boolean",
            "description": "Indicates if the subscription auto-renews.",
            "example": true
          },
          "price": {
            "type": "number",
            "format": "double",
            "description": "Price of the subscription.",
            "example": 10
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted price of the subscription.",
            "example": "$10.00"
          },
          "priceInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Price in the payout currency.",
            "example": 10
          },
          "priceInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted price in the payout currency.",
            "example": "$10.00"
          },
          "discount": {
            "type": "number",
            "format": "double",
            "description": "Discount applied to the subscription.",
            "example": 0
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted display of the discount.",
            "example": "$0.00"
          },
          "discountInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Discount in the payout currency.",
            "example": 0
          },
          "discountInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted discount in the payout currency.",
            "example": "$0.00"
          },
          "subtotal": {
            "type": "number",
            "format": "double",
            "description": "Subtotal of the subscription.",
            "example": 10
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted display of the subtotal.",
            "example": "$10.00"
          },
          "subtotalInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Subtotal in the payout currency.",
            "example": 10
          },
          "subtotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted subtotal in the payout currency.",
            "example": "$10.00"
          },
          "next": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next billing date (in milliseconds).",
            "example": 1825977600000
          },
          "nextValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next billing date in milliseconds.",
            "example": 1825977600000
          },
          "nextInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Timestamp for the next billing date in seconds.",
            "example": 1825977600
          },
          "nextDisplay": {
            "type": "string",
            "description": "Formatted display of the next billing date.",
            "example": "11/12/27"
          },
          "nextDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next billing date.",
            "example": "2027-11-12"
          },
          "end": {
            "type": "string",
            "nullable": true,
            "description": "End date of the subscription, if applicable.",
            "example": null
          },
          "endValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the end date of the subscription, if applicable.",
            "example": null
          },
          "endInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "End date of the subscription in seconds.",
            "example": null
          },
          "endDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the end date of the subscription.",
            "example": null
          },
          "endDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted end date of the subscription.",
            "example": null
          },
          "canceledDate": {
            "type": "string",
            "nullable": true,
            "description": "Date the subscription was canceled, if applicable.",
            "example": null
          },
          "canceledDateValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the canceled date of the subscription.",
            "example": null
          },
          "canceledDateInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "Timestamp of the canceled date in seconds.",
            "example": null
          },
          "canceledDateDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the canceled date.",
            "example": null
          },
          "canceledDateDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted canceled date.",
            "example": null
          },
          "deactivationDate": {
            "type": "string",
            "nullable": true,
            "description": "Date the subscription was deactivated, if applicable.",
            "example": null
          },
          "deactivationDateValue": {
            "type": "string",
            "nullable": true,
            "description": "Value of the deactivation date of the subscription.",
            "example": null
          },
          "deactivationDateInSeconds": {
            "type": "integer",
            "nullable": true,
            "description": "Deactivation date of the subscription in seconds.",
            "example": null
          },
          "deactivationDateDisplay": {
            "type": "string",
            "nullable": true,
            "description": "Formatted display of the deactivation date.",
            "example": null
          },
          "deactivationDateDisplayISO8601": {
            "type": "string",
            "nullable": true,
            "format": "date",
            "description": "ISO 8601 formatted deactivation date.",
            "example": null
          },
          "sequence": {
            "type": "integer",
            "description": "Sequence number of the subscription's billing cycle.",
            "example": 1
          },
          "periods": {
            "type": "string",
            "nullable": true,
            "description": "Total number of billing periods, if applicable.",
            "example": null
          },
          "remainingPeriods": {
            "type": "string",
            "nullable": true,
            "description": "Remaining billing periods, if applicable.",
            "example": null
          },
          "begin": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp of the subscription start date in milliseconds.",
            "example": 1731383443568
          },
          "beginValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the subscription start date in milliseconds.",
            "example": 1731383443568
          },
          "beginInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Subscription start date in seconds.",
            "example": 1731383443
          },
          "beginDisplay": {
            "type": "string",
            "description": "Formatted display of the subscription start date.",
            "example": "11/12/24"
          },
          "beginDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted subscription start date.",
            "example": "2025-01-01"
          },
          "beginDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted subscription start date.",
            "example": "Jan 01, 2025"
          },
          "beginDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted subscription start date with time.",
            "example": "Jan 01, 2025 03:50:43 AM"
          },
          "nextDisplayEmailEnhancements": {
            "type": "string",
            "description": "Email-friendly formatted next billing date.",
            "example": "Jan 01, 2027"
          },
          "nextDisplayEmailEnhancementsWithTime": {
            "type": "string",
            "description": "Email-friendly formatted next billing date with time.",
            "example": "Jan 01, 2027 12:00:00 AM"
          },
          "intervalUnit": {
            "type": "string",
            "description": "Unit of the billing interval (e.g., year, month).",
            "example": "year"
          },
          "intervalUnitAbbreviation": {
            "type": "string",
            "description": "Abbreviation for the billing interval unit.",
            "example": "yr"
          },
          "intervalLength": {
            "type": "integer",
            "description": "Length of the billing interval.",
            "example": 1
          },
          "intervalLengthGtOne": {
            "type": "boolean",
            "description": "Indicates if the interval length is greater than one.",
            "example": false
          },
          "nextChargeCurrency": {
            "type": "string",
            "description": "Currency for the next charge.",
            "example": "USD"
          },
          "nextChargeDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next charge date in milliseconds.",
            "example": 1825977600000
          },
          "nextChargeDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next charge date in milliseconds.",
            "example": 1825977600000
          },
          "nextChargeDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Next charge date in seconds.",
            "example": 1825977600
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Formatted display of the next charge date.",
            "example": "11/12/27"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next charge date.",
            "example": "2027-11-12"
          },
          "nextChargePreTax": {
            "type": "number",
            "format": "double",
            "description": "Pre-tax amount of the next charge.",
            "example": 10
          },
          "nextChargePreTaxDisplay": {
            "type": "string",
            "description": "Formatted pre-tax amount of the next charge.",
            "example": "$10.00"
          },
          "nextChargePreTaxInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Pre-tax amount of the next charge in the payout currency.",
            "example": 10
          },
          "nextChargePreTaxInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted pre-tax amount in the payout currency.",
            "example": "$10.00"
          },
          "nextChargeTotal": {
            "type": "number",
            "format": "double",
            "description": "Total amount of the next charge.",
            "example": 10
          },
          "nextChargeTotalDisplay": {
            "type": "string",
            "description": "Formatted total amount of the next charge.",
            "example": "$10.00"
          },
          "nextChargeTotalInPayoutCurrency": {
            "type": "number",
            "format": "double",
            "description": "Total amount in the payout currency.",
            "example": 10
          },
          "nextChargeTotalInPayoutCurrencyDisplay": {
            "type": "string",
            "description": "Formatted total amount in the payout currency.",
            "example": "$10.00"
          },
          "nextNotificationType": {
            "type": "string",
            "description": "Type of the next notification (e.g., PAYMENT_REMINDER).",
            "example": "PAYMENT_REMINDER"
          },
          "nextNotificationDate": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp for the next notification date in milliseconds.",
            "example": 1825372800000
          },
          "nextNotificationDateValue": {
            "type": "integer",
            "format": "int64",
            "description": "Value of the next notification date in milliseconds.",
            "example": 1825372800000
          },
          "nextNotificationDateInSeconds": {
            "type": "integer",
            "format": "int32",
            "description": "Next notification date in seconds.",
            "example": 1825372800
          },
          "nextNotificationDateDisplay": {
            "type": "string",
            "description": "Formatted display of the next notification date.",
            "example": "11/5/27"
          },
          "nextNotificationDateDisplayISO8601": {
            "type": "string",
            "format": "date",
            "description": "ISO 8601 formatted next notification date.",
            "example": "2027-11-05"
          },
          "paymentReminder": {
            "type": "object",
            "description": "Details about the payment reminder interval.",
            "properties": {
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the payment reminder interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the payment reminder interval.",
                "example": 1
              }
            }
          },
          "paymentOverdue": {
            "type": "object",
            "description": "Details about overdue payment settings.",
            "properties": {
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the overdue payment interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the overdue payment interval.",
                "example": 1
              },
              "total": {
                "type": "integer",
                "description": "Total number of overdue payment intervals allowed.",
                "example": 4
              },
              "sent": {
                "type": "integer",
                "description": "Number of overdue payment notifications already sent.",
                "example": 0
              }
            }
          },
          "cancellationSetting": {
            "type": "object",
            "description": "Settings related to subscription cancellation.",
            "properties": {
              "cancellation": {
                "type": "string",
                "description": "Type of cancellation setting (e.g., AFTER_LAST_NOTIFICATION).",
                "example": "AFTER_LAST_NOTIFICATION"
              },
              "intervalUnit": {
                "type": "string",
                "description": "Unit of the cancellation interval (e.g., week, month).",
                "example": "week"
              },
              "intervalLength": {
                "type": "integer",
                "description": "Length of the cancellation interval.",
                "example": 1
              }
            }
          },
          "fulfillments": {
            "type": "object",
            "description": "Fulfillment details for the subscription.",
            "additionalProperties": true
          },
          "instructions": {
            "type": "array",
            "description": "Array of instruction objects providing details about subscription periods.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Identifier for the product associated with the instruction.",
                  "example": "furious-falcon-annual-subscription"
                },
                "type": {
                  "type": "string",
                  "description": "Type of the instruction (e.g., regular, discounted).",
                  "example": "regular"
                },
                "isNotTrial": {
                  "type": "boolean",
                  "description": "Indicates whether the instruction is not for a trial period.",
                  "example": true
                },
                "periodStartDate": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Timestamp of the period start date in milliseconds.",
                  "example": 1731369600000
                },
                "periodStartDateValue": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Value of the period start date in milliseconds.",
                  "example": 1731369600000
                },
                "periodStartDateInSeconds": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Period start date in seconds.",
                  "example": 1731369600
                },
                "periodStartDateDisplay": {
                  "type": "string",
                  "description": "Formatted display of the period start date.",
                  "example": "11/12/24"
                },
                "periodStartDateDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO 8601 formatted period start date.",
                  "example": "2025-01-01"
                },
                "periodEndDate": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Timestamp of the period end date in milliseconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateValue": {
                  "type": "integer",
                  "format": "int64",
                  "description": "Value of the period end date in milliseconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateInSeconds": {
                  "type": "integer",
                  "format": "int32",
                  "description": "Period end date in seconds. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateDisplay": {
                  "type": "string",
                  "description": "Formatted display of the period end date. Can be null if not applicable.",
                  "example": null
                },
                "periodEndDateDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO 8601 formatted period end date. Can be null if not applicable.",
                  "example": null
                },
                "intervalUnit": {
                  "type": "string",
                  "description": "Unit of the subscription interval (e.g., year, month).",
                  "example": "year"
                },
                "intervalLength": {
                  "type": "integer",
                  "description": "Length of the subscription interval.",
                  "example": 1
                },
                "discountPercent": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount percentage applied during the period.",
                  "example": 0
                },
                "discountPercentValue": {
                  "type": "number",
                  "format": "double",
                  "description": "Value of the discount percentage.",
                  "example": 0
                },
                "discountPercentDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount percentage.",
                  "example": "0%"
                },
                "discountTotal": {
                  "type": "number",
                  "format": "double",
                  "description": "Total discount amount during the period.",
                  "example": 0
                },
                "discountTotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total discount amount.",
                  "example": "$0.00"
                },
                "discountTotalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total discount amount in payout currency during the period.",
                  "example": 0
                },
                "discountTotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total discount amount in payout currency.",
                  "example": "$0.00"
                },
                "unitDiscount": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount per unit during the period.",
                  "example": 0
                },
                "unitDiscountDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit.",
                  "example": "$0.00"
                },
                "unitDiscountInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Discount per unit in payout currency during the period.",
                  "example": 0
                },
                "unitDiscountInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the discount per unit in payout currency.",
                  "example": "$0.00"
                },
                "price": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit during the period.",
                  "example": 10
                },
                "priceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit.",
                  "example": "$10.00"
                },
                "priceInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit in payout currency during the period.",
                  "example": 10
                },
                "priceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit in payout currency.",
                  "example": "$10.00"
                },
                "priceTotal": {
                  "type": "number",
                  "format": "double",
                  "description": "Total price during the period.",
                  "example": 10
                },
                "priceTotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total price.",
                  "example": "$10.00"
                },
                "priceTotalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total price in payout currency during the period.",
                  "example": 10
                },
                "priceTotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total price in payout currency.",
                  "example": "$10.00"
                },
                "unitPrice": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit during the period.",
                  "example": 10
                },
                "unitPriceDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit.",
                  "example": "$10.00"
                },
                "unitPriceInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Price per unit in payout currency during the period.",
                  "example": 10
                },
                "unitPriceInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the price per unit in payout currency.",
                  "example": "$10.00"
                },
                "total": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount for the period.",
                  "example": 10
                },
                "totalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount.",
                  "example": "$10.00"
                },
                "totalInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount in payout currency during the period.",
                  "example": 10
                },
                "totalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount in payout currency.",
                  "example": "$10.00"
                },
                "totalWithTaxes": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount including taxes for the period.",
                  "example": 10
                },
                "totalWithTaxesDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount including taxes.",
                  "example": "$10.00"
                },
                "totalWithTaxesInPayoutCurrency": {
                  "type": "number",
                  "format": "double",
                  "description": "Total amount including taxes in payout currency during the period.",
                  "example": 10
                },
                "totalWithTaxesInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total amount including taxes in payout currency.",
                  "example": "$10.00"
                }
              }
            }
          },
          "initialOrderId": {
            "type": "string",
            "description": "Unique identifier for the initial order associated with the subscription.",
            "example": "9_fopjeBRBuCgCz0Nm2VXw"
          },
          "initialOrderReference": {
            "type": "string",
            "description": "Reference number for the initial order associated with the subscription.",
            "example": "NKO241112-7593-98143"
          },
          "action": {
            "type": "string",
            "description": "Action performed on the subscription.",
            "example": "subscriptions.resume.get"
          },
          "result": {
            "type": "string",
            "description": "Result of the action performed, indicating success or failure.",
            "example": "success"
          }
        }
      },
      "ResumeSubscriptionErrorResponse": {
        "type": "object",
        "title": "PauseSubscriptionErrorResponse",
        "description": "Error response schema when resuming a subscription fails.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action that was attempted.",
            "example": "subscriptions.resume"
          },
          "result": {
            "type": "string",
            "description": "The result of the action, indicating an error.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error that occurred.",
            "properties": {
              "subscriptions.resume": {
                "type": "string",
                "description": "Error message specific to the pause subscription action.",
                "example": "Unable to find subscription."
              }
            }
          }
        }
      }
    }
  }
}
```

Convert a subscription

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Convert a subscription

Creates a session that allows a buyer to reactivate a trial subscription and convert it to a paid subscription.

**Preconditions:**

- The subscription is a trial without any payment method added.
- The subscription was deactivated because the trial period ended without a payment method being added.
- The subscription conversion period is still active.
- Conversion is enabled for the subscription.
- The reactivation request is made within the allowed conversion period: Deactivation date + conversion days <= API call date.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}/convert": {
      "post": {
        "summary": "Convert a subscription",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "ConvertExpiredTrialWithoutPaymentMethod",
        "description": "Creates a session that allows a buyer to reactivate a trial subscription and convert it to a paid subscription.\n\n**Preconditions:**\n\n- The subscription is a trial without any payment method added.\n- The subscription was deactivated because the trial period ended without a payment method being added.\n- The subscription conversion period is still active.\n- Conversion is enabled for the subscription.\n- The reactivation request is made within the allowed conversion period: Deactivation date + conversion days <= API call date.\n",
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          }
        ],
        "deprecated": false,
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ConvertExpiredTrialWithoutPaymentMethodResponse"
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
                      "$ref": "#/components/schemas/UnableFindErrorResponse"
                    },
                    {
                      "$ref": "#/components/schemas/UnableReactivateErrorResponse"
                    },
                    {
                      "$ref": "#/components/schemas/OnlyCanceledErrorResponse"
                    },
                    {
                      "$ref": "#/components/schemas/OnlyDeactivatedErrorResponse"
                    }
                  ]
                },
                "examples": {
                  "UnableToFindSubscriptionExample": {
                    "summary": "Unable to Find Subscription Error",
                    "value": {
                      "action": "subscription.convert",
                      "result": "error",
                      "error": {
                        "subscription.convert": "Unable to find subscription."
                      }
                    }
                  },
                  "UnableToReactivateSubscriptionExample": {
                    "summary": "Unable to Reactivate Subscription Error",
                    "value": {
                      "action": "subscription.convert",
                      "result": "error",
                      "error": {
                        "subscription.convert": "Unable to reactivate. Please specify a subscription where payment was not collected."
                      }
                    }
                  },
                  "OnlyCanceledSubscriptionExample": {
                    "summary": "Only Canceled Subscriptions Error",
                    "value": {
                      "action": "subscription.convert",
                      "result": "error",
                      "error": {
                        "subscription.convert": "Only canceled subscriptions can be reactivated."
                      }
                    }
                  },
                  "OnlyDeactivatedSubscriptionExample": {
                    "summary": "Only Deactivated Subscriptions Error",
                    "value": {
                      "action": "subscription.convert",
                      "result": "error",
                      "error": {
                        "subscription.convert": "Only deactivated subscriptions can be reactivated."
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
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "ConvertExpiredTrialWithoutPaymentMethodResponse": {
        "type": "object",
        "description": "Response schema for reactivating a trial subscription.",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the reactivation session.",
            "example": "1665462758431"
          },
          "currency": {
            "type": "string",
            "description": "The currency used for the subscription.",
            "example": "USD"
          },
          "expires": {
            "type": "integer",
            "format": "int64",
            "description": "Timestamp (in milliseconds) when the session expires.",
            "example": 1665549158235
          },
          "order": {
            "type": "string",
            "nullable": true,
            "description": "Unique identifier for the order, if any.",
            "example": null
          },
          "account": {
            "type": "string",
            "description": "Unique identifier for the buyer's account.",
            "example": "abCdE1FGH2Hij3KLMnOpqR"
          },
          "subtotal": {
            "type": "number",
            "format": "double",
            "description": "Subtotal amount for the subscription.",
            "example": 14.99
          },
          "items": {
            "type": "array",
            "description": "List of items associated with the subscription.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "Unique identifier to reference a specific product, also known as the product path ID.",
                  "example": "monthly-magazine"
                },
                "quantity": {
                  "type": "integer",
                  "description": "Quantity of the product.",
                  "example": 1
                }
              }
            }
          }
        }
      },
      "UnableFindErrorResponse": {
        "title": "Unable to Find Subscription Error",
        "type": "object",
        "description": "Error response when the subscription cannot be found.",
        "properties": {
          "action": {
            "type": "string",
            "description": "Action being performed.",
            "example": "subscription.convert"
          },
          "result": {
            "type": "string",
            "description": "Result of the action.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error encountered.",
            "properties": {
              "subscription.convert": {
                "type": "string",
                "description": "Error message indicating that the subscription could not be found.",
                "example": "Unable to find subscription."
              }
            }
          }
        }
      },
      "UnableReactivateErrorResponse": {
        "title": "Unable to Reactivate Subscription Error",
        "type": "object",
        "description": "Error response when attempting to reactivate a subscription where payment was collected.",
        "properties": {
          "action": {
            "type": "string",
            "description": "Action being performed.",
            "example": "subscription.convert"
          },
          "result": {
            "type": "string",
            "description": "Result of the action.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error encountered.",
            "properties": {
              "subscription.convert": {
                "type": "string",
                "description": "Error message indicating that reactivation is not possible due to payment collection.",
                "example": "Unable to reactivate. Please specify a subscription where payment was not collected."
              }
            }
          }
        }
      },
      "OnlyCanceledErrorResponse": {
        "title": "Only Canceled Subscriptions Error",
        "type": "object",
        "description": "Error response when attempting to reactivate a subscription that is not canceled.",
        "properties": {
          "action": {
            "type": "string",
            "description": "Action being performed.",
            "example": "subscription.convert"
          },
          "result": {
            "type": "string",
            "description": "Result of the action.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error encountered.",
            "properties": {
              "subscription.convert": {
                "type": "string",
                "description": "Error message indicating that only canceled subscriptions can be reactivated.",
                "example": "Only canceled subscriptions can be reactivated."
              }
            }
          }
        }
      },
      "OnlyDeactivatedErrorResponse": {
        "title": "Only Deactivated Subscriptions Error",
        "type": "object",
        "description": "Error response when attempting to reactivate a subscription that is not deactivated.",
        "properties": {
          "action": {
            "type": "string",
            "description": "Action being performed.",
            "example": "subscription.convert"
          },
          "result": {
            "type": "string",
            "description": "Result of the action.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "description": "Details about the error encountered.",
            "properties": {
              "subscription.convert": {
                "type": "string",
                "description": "Error message indicating that only deactivated subscriptions can be reactivated.",
                "example": "Only deactivated subscriptions can be reactivated."
              }
            }
          }
        }
      }
    }
  }
}
```

List all subscription plan changes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all subscription plan changes

Returns an array of subscription plan change history entries, detailing what changed, when the change occurred, and how it was modified.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/{subscription_id}/history": {
      "get": {
        "summary": "List all subscription plan changes",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "GetsubscriptionplanC=changehistory",
        "description": "Returns an array of subscription plan change history entries, detailing what changed, when the change occurred, and how it was modified.\n",
        "deprecated": false,
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          },
          {
            "name": "scope",
            "in": "query",
            "required": false,
            "description": "Specify the item type to filter results. Valid values include `base_plan` and `add_on`.",
            "schema": {
              "type": "string",
              "enum": [
                "base_plan",
                "add_on"
              ]
            }
          },
          {
            "name": "order",
            "in": "query",
            "required": false,
            "description": "Specify the sort order of the results. Valid values include `increasing` and `decreasing`. The default value is `decreasing`.\n",
            "schema": {
              "type": "string",
              "enum": [
                "increasing",
                "decreasing"
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
                  "$ref": "#/components/schemas/SubscriptionHistoryResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SubscriptionHistoryErrorResponse"
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
      "SubscriptionHistoryResponse": {
        "type": "object",
        "description": "Response schema for subscription changes.",
        "properties": {
          "currency": {
            "type": "string",
            "description": "The currency in which the transactions are made.",
            "example": "USD"
          },
          "timezone": {
            "type": "string",
            "description": "The timezone associated with the changes.",
            "example": "UTC"
          },
          "changes": {
            "type": "array",
            "description": "List of changes associated with the subscription.",
            "items": {
              "type": "object",
              "properties": {
                "insertTimestamp": {
                  "type": "integer",
                  "format": "int64",
                  "description": "The timestamp when the change was inserted.",
                  "example": 1684378979000
                },
                "subscriptionId": {
                  "type": "string",
                  "description": "Unique identifier for the subscription.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "siteId": {
                  "type": "string",
                  "description": "Unique identifier for the site.",
                  "example": "aBCDE12fGH"
                },
                "changeId": {
                  "type": "string",
                  "description": "Unique identifier for the change.",
                  "example": "AbC1D2eFGH34ijklmnopQrs"
                },
                "prorated": {
                  "type": "boolean",
                  "description": "Indicates if the change is prorated.",
                  "example": true
                },
                "orderId": {
                  "type": "string",
                  "description": "Unique identifier for the order associated with the change.",
                  "example": "AbC1D2eFGH34ijklmnopQrs"
                },
                "orderRef": {
                  "type": "string",
                  "nullable": true,
                  "description": "Reference for the order associated with the change.",
                  "example": null
                },
                "itemType": {
                  "type": "string",
                  "description": "Type of item associated with the change.",
                  "example": "ADD_ON"
                },
                "itemId": {
                  "type": "string",
                  "description": "Unique identifier for the item.",
                  "example": "AbC1D2eFGH34ijklmnopQrs"
                },
                "itemProductPath": {
                  "type": "string",
                  "description": "Path of the product associated with the item.",
                  "example": "document-viewer"
                },
                "itemProductDisplay": {
                  "type": "string",
                  "description": "Display name of the product associated with the item.",
                  "example": "Document Viewer"
                },
                "itemProductSku": {
                  "type": "string",
                  "description": "SKU of the product associated with the item.",
                  "example": "SKU-12345"
                },
                "changeType": {
                  "type": "string",
                  "description": "Type of change made to the subscription.",
                  "example": "ADD_ON_REMOVE"
                },
                "changedFrom": {
                  "type": "string",
                  "description": "Previous state or quantity of the item.",
                  "example": "1"
                },
                "changedFromDisplay": {
                  "type": "string",
                  "description": "Display representation of the previous state or quantity.",
                  "example": "1"
                },
                "changedTo": {
                  "type": "string",
                  "description": "Updated state or quantity of the item.",
                  "example": "0"
                },
                "changedToDisplay": {
                  "type": "string",
                  "description": "Display representation of the updated state or quantity.",
                  "example": "0"
                }
              }
            }
          }
        }
      },
      "SubscriptionHistoryErrorResponse": {
        "type": "object",
        "description": "Error response when a subscription history cannot be retrieved.",
        "properties": {
          "subscriptions": {
            "type": "array",
            "description": "Array containing details of the subscription and error.",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "Action being performed.",
                  "example": "subscription.get"
                },
                "subscription": {
                  "type": "string",
                  "description": "The unique identifier of the subscription.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "result": {
                  "type": "string",
                  "description": "Result of the action.",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "description": "Details about the error encountered.",
                  "properties": {
                    "subscription": {
                      "type": "string",
                      "description": "The error message related to the subscription.",
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
Create a co-term group

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create a co-term group

Creates a new co-term group for eligible subscriptions under the same account.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm": {
      "post": {
        "summary": "Create a co-term group",
        "description": "Creates a new co-term group for eligible subscriptions under the same account.\n",
        "operationId": "createCotermGroup",
        "tags": [
          "Subscriptions"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateCoTermGroupRequest"
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
                  "$ref": "#/components/schemas/CoTermCreateResponse"
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
      "CreateCoTermGroupRequest": {
        "type": "object",
        "description": "Request body to create a new co-term group.",
        "required": [
          "accountId",
          "coTermGroup"
        ],
        "properties": {
          "accountId": {
            "type": "string",
            "description": "The ID of the account the subscriptions belong to.",
            "example": "Mn7Op8Qr9St0Uv1Wx2Yz3A"
          },
          "coTermGroup": {
            "type": "object",
            "description": "Container for the group details.",
            "properties": {
              "displayName": {
                "type": "string",
                "description": "A human-readable name for the group.",
                "example": "Example co-term group name"
              },
              "subscriptions": {
                "type": "array",
                "description": "List of Subscription IDs to include in the group.",
                "items": {
                  "type": "string",
                  "description": "A subscription ID.",
                  "example": "Ab1C-DeFgH-iJ2K3l4MmNo"
                }
              }
            }
          }
        }
      },
      "CoTermCreateResponse": {
        "type": "object",
        "description": "Response returned upon successful creation of a group.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.create"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "accountId": {
            "type": "string",
            "description": "Account ID associated with the group.",
            "example": "Mn7Op8Qr9St0Uv1Wx2Yz3A"
          },
          "coTermGroup": {
            "$ref": "#/components/schemas/CoTermGroupObject"
          }
        }
      },
      "CoTermGroupObject": {
        "type": "object",
        "description": "Nested object representing the group data in a Create response.",
        "properties": {
          "coTermGroupId": {
            "type": "string",
            "description": "Unique identifier for the co-term group.",
            "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
          },
          "displayName": {
            "type": "string",
            "description": "Display name of the group.",
            "example": "Example co-term group name"
          },
          "groupingCriteria": {
            "type": "array",
            "description": "List of criteria used to validate the group.",
            "items": {
              "$ref": "#/components/schemas/GroupingCriteria"
            }
          },
          "subscriptions": {
            "type": "array",
            "description": "List of subscriptions in the group.",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "The subscription ID.",
                  "example": "Ab1C-DeFgH-iJ2K3l4MmNo"
                },
                "status": {
                  "type": "string",
                  "description": "The status of the subscription within the group.",
                  "example": "Co-Termed"
                }
              }
            }
          }
        }
      },
      "GroupingCriteria": {
        "type": "object",
        "description": "The criteria used to group subscriptions (currency, interval, etc).",
        "properties": {
          "interval": {
            "type": "array",
            "description": "Allowed billing intervals.",
            "items": {
              "type": "object",
              "properties": {
                "unit": {
                  "type": "string",
                  "description": "The interval unit.",
                  "example": "MONTH"
                },
                "length": {
                  "type": "integer",
                  "description": "The interval length.",
                  "example": 1
                }
              }
            }
          },
          "currency": {
            "type": "array",
            "description": "Allowed currencies.",
            "items": {
              "type": "string",
              "description": "Currency code.",
              "example": "USD"
            }
          },
          "paymentMethods": {
            "type": "array",
            "description": "Allowed payment methods.",
            "items": {
              "type": "object",
              "properties": {
                "type": {
                  "type": "string",
                  "description": "Payment method type.",
                  "example": "card"
                },
                "ending": {
                  "type": "string",
                  "description": "Display string for the payment method.",
                  "example": "*4242 (USD)"
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

Generate co-term group estimate

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate co-term group estimate

Calculates proration, tax, and next charge amounts for a co-term group without executing the changes.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm/{cotermGroupId}/estimate": {
      "post": {
        "summary": "Generate co-term group estimate",
        "description": "Calculates proration, tax, and next charge amounts for a co-term group without executing the changes.\n",
        "operationId": "estimateCotermGroup",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "cotermGroupId",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the co-term group.",
            "schema": {
              "type": "string",
              "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CoTermEstimateResponse"
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
      "CoTermEstimateResponse": {
        "type": "object",
        "description": "Response containing pricing and proration estimates.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.estimate"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "coTermGroupId": {
            "type": "string",
            "description": "Unique identifier for the co-term group.",
            "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
          },
          "account": {
            "type": "string",
            "description": "Account ID associated with the group.",
            "example": "Mn7Op8Qr9St0Uv1Wx2Yz3A"
          },
          "currency": {
            "type": "string",
            "description": "The currency code for the estimate.",
            "example": "USD"
          },
          "UTC": {
            "type": "string",
            "description": "The timezone used for the estimate dates.",
            "example": "UTC"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of the estimated period start.",
            "example": 1770076800000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the period start date.",
            "example": "2/3/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted string of the period start date.",
            "example": "2026-02-03"
          },
          "periodEndDate": {
            "type": "integer",
            "description": "Timestamp of the estimated period end.",
            "example": 1772409600000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the period end date.",
            "example": "3/2/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted string of the period end date.",
            "example": "2026-03-02"
          },
          "subscriptions": {
            "type": "array",
            "description": "Detailed breakdown of the estimate per subscription.",
            "items": {
              "$ref": "#/components/schemas/SubscriptionEstimate"
            }
          },
          "amountDueTotal": {
            "$ref": "#/components/schemas/AmountDue"
          }
        }
      },
      "SubscriptionEstimate": {
        "type": "object",
        "description": "Estimation details for a single subscription.",
        "properties": {
          "subscription": {
            "type": "string",
            "description": "The subscription ID.",
            "example": "Ab1C-DeFgH-iJ2K3l4MmNo"
          },
          "currency": {
            "type": "string",
            "description": "Currency code.",
            "example": "USD"
          },
          "timezone": {
            "type": "string",
            "description": "Timezone code.",
            "example": "UTC"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of the period start.",
            "example": 1769990400000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Display string for period start.",
            "example": "2/2/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period start.",
            "example": "2026-02-02"
          },
          "periodEndDate": {
            "type": "integer",
            "description": "Timestamp of the period end.",
            "example": 1772323200000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Display string for period end.",
            "example": "3/1/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period end.",
            "example": "2026-03-01"
          },
          "remainingPeriods": {
            "type": "integer",
            "description": "Number of periods remaining.",
            "example": -1
          },
          "nextChargeDate": {
            "type": "integer",
            "description": "Timestamp of the next charge.",
            "example": 1772496000000
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Display string for next charge.",
            "example": "3/3/26"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for next charge.",
            "example": "2026-03-03"
          },
          "isProratable": {
            "type": "boolean",
            "description": "Whether the subscription supports proration.",
            "example": true
          },
          "prorationStatus": {
            "type": "string",
            "description": "Status of the proration calculation.",
            "example": "Available"
          },
          "currentPlan": {
            "$ref": "#/components/schemas/PlanSnapshot"
          },
          "proposedPlan": {
            "$ref": "#/components/schemas/PlanSnapshot"
          },
          "amountDue": {
            "$ref": "#/components/schemas/AmountDue"
          },
          "discountTotals": {
            "$ref": "#/components/schemas/DiscountTotals"
          },
          "status": {
            "type": "string",
            "description": "Overall status of the estimate for this subscription.",
            "example": "Ready to Prorate"
          }
        }
      },
      "PlanSnapshot": {
        "type": "object",
        "description": "Snapshot of the pricing plan for estimation.",
        "properties": {
          "display": {
            "type": "string",
            "description": "Display name of the product/plan.",
            "example": "Furious Falcon Analytics"
          },
          "product": {
            "type": "string",
            "description": "Product path ID.",
            "example": "furious-falcon-analytics"
          },
          "billingFrequency": {
            "type": "string",
            "description": "Frequency of the billing cycle.",
            "example": "1 month"
          },
          "price": {
            "type": "number",
            "description": "Unit price of the product.",
            "example": 100
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted unit price.",
            "example": "$100.00"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount applied.",
            "example": 0
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted discount amount.",
            "example": "$0.00"
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the product.",
            "example": 1
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal before tax.",
            "example": 100
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted subtotal.",
            "example": "$100.00"
          },
          "tax": {
            "type": "number",
            "description": "Tax amount.",
            "example": 8
          },
          "taxDisplay": {
            "type": "string",
            "description": "Formatted tax amount.",
            "example": "$8.00"
          },
          "total": {
            "type": "number",
            "description": "Total amount including tax.",
            "example": 108
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted total amount.",
            "example": "$108.00"
          },
          "taxPercent": {
            "type": "number",
            "description": "Tax percentage applied.",
            "example": 8
          },
          "taxPercentDisplay": {
            "type": "string",
            "description": "Formatted tax percentage.",
            "example": "8%"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of plan period start.",
            "example": 1769990400000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Display string for period start.",
            "example": "2/2/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period start.",
            "example": "2026-02-02"
          },
          "periodEndDate": {
            "type": "integer",
            "description": "Timestamp of plan period end.",
            "example": 1769990400000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Display string for period end.",
            "example": "2/2/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period end.",
            "example": "2026-02-02"
          },
          "prorationUtilizedDays": {
            "type": "integer",
            "description": "Number of days utilized in the proration.",
            "example": 0
          },
          "prorationRemainingDays": {
            "type": "integer",
            "description": "Number of days remaining in the proration.",
            "example": 28
          },
          "prorationTotalDays": {
            "type": "integer",
            "description": "Total days in the proration period.",
            "example": 28
          },
          "proratedItemCharge": {
            "type": "number",
            "description": "Charge amount for the prorated period.",
            "example": 103.57
          },
          "proratedItemChargeDisplay": {
            "type": "string",
            "description": "Formatted prorated charge.",
            "example": "$103.57"
          },
          "proratedItemCredit": {
            "type": "number",
            "description": "Credit amount for the prorated period.",
            "example": 100
          },
          "proratedItemCreditDisplay": {
            "type": "string",
            "description": "Formatted prorated credit.",
            "example": "$100.00"
          },
          "proratedItemSubtotal": {
            "type": "number",
            "description": "Subtotal of proration adjustments.",
            "example": 3.57
          },
          "proratedItemSubtotalDisplay": {
            "type": "string",
            "description": "Formatted proration subtotal.",
            "example": "$3.57"
          },
          "proratedItemTax": {
            "type": "number",
            "description": "Tax on the prorated amount.",
            "example": 0.28
          },
          "proratedItemTaxDisplay": {
            "type": "string",
            "description": "Formatted proration tax.",
            "example": "$0.28"
          },
          "proratedItemTotal": {
            "type": "number",
            "description": "Total prorated amount.",
            "example": 3.85
          },
          "proratedItemTotalDisplay": {
            "type": "string",
            "description": "Formatted total prorated amount.",
            "example": "$3.85"
          },
          "addons": {
            "type": "array",
            "description": "List of add-ons included in the plan.",
            "items": {
              "$ref": "#/components/schemas/AddonSnapshot"
            }
          },
          "subscriptionSubtotal": {
            "type": "number",
            "description": "Subscription subtotal.",
            "example": 100
          },
          "subscriptionSubtotalDisplay": {
            "type": "string",
            "description": "Formatted subscription subtotal.",
            "example": "$100.00"
          },
          "subscriptionTax": {
            "type": "number",
            "description": "Subscription tax.",
            "example": 8
          },
          "subscriptionTaxDisplay": {
            "type": "string",
            "description": "Formatted subscription tax.",
            "example": "$8.00"
          },
          "subscriptionTotal": {
            "type": "number",
            "description": "Subscription total.",
            "example": 108
          },
          "subscriptionTotalDisplay": {
            "type": "string",
            "description": "Formatted subscription total.",
            "example": "$108.00"
          },
          "subscriptionProratedCredit": {
            "type": "number",
            "description": "Credit amount for subscription proration.",
            "example": 100
          },
          "subscriptionProratedCreditDisplay": {
            "type": "string",
            "description": "Formatted credit amount.",
            "example": "$100.00"
          },
          "subscriptionProratedCharge": {
            "type": "number",
            "description": "Charge amount for subscription proration.",
            "example": 103.57
          },
          "subscriptionProratedChargeDisplay": {
            "type": "string",
            "description": "Formatted charge amount.",
            "example": "$103.57"
          }
        }
      },
      "AddonSnapshot": {
        "type": "object",
        "description": "Snapshot of an add-on product.",
        "properties": {
          "display": {
            "type": "string",
            "description": "Display name of the add-on.",
            "example": "Addon Name"
          },
          "product": {
            "type": "string",
            "description": "Product path ID of the add-on.",
            "example": "addon-product-path"
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted price of the add-on.",
            "example": "$5.00"
          },
          "proratedItemChargeDisplay": {
            "type": "string",
            "description": "Formatted prorated charge for the add-on.",
            "example": "$2.50"
          }
        }
      },
      "AmountDue": {
        "type": "object",
        "description": "Summary of amounts due.",
        "properties": {
          "prorationSubtotal": {
            "type": "number",
            "description": "Subtotal of proration amounts.",
            "example": 3.57
          },
          "prorationSubtotalDisplay": {
            "type": "string",
            "description": "Formatted proration subtotal.",
            "example": "$3.57"
          },
          "prorationTax": {
            "type": "number",
            "description": "Tax on proration amounts.",
            "example": 0.28
          },
          "prorationTaxDisplay": {
            "type": "string",
            "description": "Formatted proration tax.",
            "example": "$0.28"
          },
          "totalAmountDue": {
            "type": "number",
            "description": "Total amount due now.",
            "example": 3.85
          },
          "totalAmountDueDisplay": {
            "type": "string",
            "description": "Formatted total amount due.",
            "example": "$3.85"
          },
          "nextChargeDate": {
            "type": "integer",
            "description": "Timestamp of the next charge.",
            "example": 1772496000000
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Display string for next charge date.",
            "example": "3/3/26"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for next charge date.",
            "example": "2026-03-03"
          },
          "nextChargeAmount": {
            "type": "number",
            "description": "Amount of the next charge.",
            "example": 108
          },
          "nextChargeAmountDisplay": {
            "type": "string",
            "description": "Formatted amount of the next charge.",
            "example": "$108.00"
          }
        }
      },
      "DiscountTotals": {
        "type": "object",
        "description": "Summary of discount totals.",
        "properties": {
          "productLevelDiscountTotal": {
            "type": "number",
            "description": "Total product-level discounts.",
            "example": 0
          },
          "productLevelDiscountTotalDisplay": {
            "type": "string",
            "description": "Formatted total product-level discounts.",
            "example": "$0.00"
          },
          "couponLevelDiscountTotal": {
            "type": "number",
            "description": "Total coupon-level discounts.",
            "example": 0
          },
          "couponLevelDiscountTotalDisplay": {
            "type": "string",
            "description": "Formatted total coupon-level discounts.",
            "example": "$0.00"
          }
        }
      }
    }
  }
}
```

Execute co-term group changes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Execute co-term group changes

Finalizes the co-term group and applies billing changes.

This operation generates an Order ID and returns the new aligned billing period dates for the group.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm/{cotermGroupId}/execute": {
      "post": {
        "summary": "Execute co-term group changes",
        "description": "Finalizes the co-term group and applies billing changes.\n\nThis operation generates an Order ID and returns the new aligned billing period dates for the group.\n",
        "operationId": "executeCotermGroup",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "cotermGroupId",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the co-term group.",
            "schema": {
              "type": "string",
              "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CoTermExecutionResponse"
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
      "CoTermExecutionResponse": {
        "type": "object",
        "description": "Response returned after executing a co-term estimation.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.execute"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "coTermGroupId": {
            "type": "string",
            "description": "Unique identifier for the co-term group.",
            "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
          },
          "order": {
            "type": "string",
            "description": "The ID of the order generated by this execution.",
            "example": "JQVCfHTZTSSyyV4c2D20Iw"
          },
          "prorated": {
            "type": "boolean",
            "description": "Indicates if proration was applied.",
            "example": true
          },
          "timezone": {
            "type": "string",
            "description": "Timezone used for the period calculations.",
            "example": "UTC"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of the new aligned period start.",
            "example": 1770076800000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the period start date.",
            "example": "2/3/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted string of the period start date.",
            "example": "2026-02-03"
          },
          "periodEndDate": {
            "type": "integer",
            "description": "Timestamp of the new aligned period end.",
            "example": 1772409600000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the period end date.",
            "example": "3/2/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted string of the period end date.",
            "example": "2026-03-02"
          },
          "subscriptions": {
            "type": "array",
            "description": "List of Subscription IDs successfully executed.",
            "items": {
              "type": "string",
              "description": "A subscription ID.",
              "example": "Ab1C-DeFgH-iJ2K3l4MmNo"
            }
          }
        }
      }
    }
  }
}
```

Retrieve a co-term group

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a co-term group

Retrieves details for a specific co-term group using its ID.

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm/{cotermGroupId}": {
      "get": {
        "summary": "Retrieve a co-term group",
        "operationId": "getCotermGroup",
        "description": "Retrieves details for a specific co-term group using its ID.",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "cotermGroupId",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the co-term group.",
            "schema": {
              "type": "string",
              "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CoTermDetailResponse"
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
      "CoTermDetailResponse": {
        "type": "object",
        "description": "Flattened response structure used when retrieving group details via GET.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.get"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "coTermGroupId": {
            "type": "string",
            "description": "Unique identifier for the co-term group.",
            "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
          },
          "displayName": {
            "type": "string",
            "description": "Display name of the group.",
            "example": "Example co-term group name"
          },
          "account": {
            "type": "string",
            "description": "Account ID associated with the group.",
            "example": "Mn7Op8Qr9St0Uv1Wx2Yz3A"
          },
          "initialOrder": {
            "type": "string",
            "description": "The ID of the order that initiated the group.",
            "example": "JQVCfHTZTSSyyV4c2D20Iw"
          },
          "nextPeriodDate": {
            "type": "integer",
            "description": "Timestamp of the next billing period start.",
            "example": 1772496000000
          },
          "nextPeriodDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the next billing date.",
            "example": "3/3/26"
          },
          "nextPeriodDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted string of the next billing date.",
            "example": "2026-03-03"
          },
          "groupingCriteria": {
            "$ref": "#/components/schemas/GroupingCriteria"
          },
          "subscriptions": {
            "type": "array",
            "description": "List of subscriptions currently in the group.",
            "items": {
              "$ref": "#/components/schemas/SubscriptionDetail"
            }
          }
        }
      },
      "GroupingCriteria": {
        "type": "object",
        "description": "The criteria used to group subscriptions (currency, interval, etc).",
        "properties": {
          "interval": {
            "type": "array",
            "description": "Allowed billing intervals.",
            "items": {
              "type": "object",
              "properties": {
                "unit": {
                  "type": "string",
                  "description": "The interval unit.",
                  "example": "MONTH"
                },
                "length": {
                  "type": "integer",
                  "description": "The interval length.",
                  "example": 1
                }
              }
            }
          },
          "currency": {
            "type": "array",
            "description": "Allowed currencies.",
            "items": {
              "type": "string",
              "description": "Currency code.",
              "example": "USD"
            }
          },
          "paymentMethods": {
            "type": "array",
            "description": "Allowed payment methods.",
            "items": {
              "type": "object",
              "properties": {
                "type": {
                  "type": "string",
                  "description": "Payment method type.",
                  "example": "card"
                },
                "ending": {
                  "type": "string",
                  "description": "Display string for the payment method.",
                  "example": "*4242 (USD)"
                }
              }
            }
          }
        }
      },
      "SubscriptionDetail": {
        "type": "object",
        "description": "Detailed information about a subscription in a group.",
        "properties": {
          "subscription": {
            "type": "string",
            "description": "The subscription ID.",
            "example": "Qr5St6Uv7Wx8Yz9A0Bc1De"
          },
          "baseSubscriptionProduct": {
            "type": "string",
            "description": "The product path associated with the subscription.",
            "example": "furious-falcon-pro"
          },
          "baseSubscriptionProductDisplay": {
            "type": "string",
            "description": "The display name of the product.",
            "example": "Furious Falcon Pro"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of the current period start.",
            "example": 1770076800000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the start date.",
            "example": "2/3/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted start date.",
            "example": "2026-02-03"
          },
          "nextPeriodDate": {
            "type": "integer",
            "description": "Timestamp of the next billing date.",
            "example": 1772496000000
          },
          "nextPeriodDateDisplay": {
            "type": "string",
            "description": "Human-readable display of the next billing date.",
            "example": "3/3/26"
          },
          "nextPeriodDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 formatted next billing date.",
            "example": "2026-03-03"
          },
          "coTermStatus": {
            "type": "string",
            "description": "Current status of the subscription relative to co-terming.",
            "example": "Executed"
          },
          "renewalAmount": {
            "type": "string",
            "description": "The formatted renewal amount.",
            "example": "$100.00"
          }
        }
      }
    }
  }
}
```

Update co-term group

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update co-term group

Add or remove subscriptions from an existing co-term group.

Use the `action` parameter to specify "ADD" or "REMOVE".


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm/{cotermGroupId}": {
      "post": {
        "summary": "Update co-term group",
        "description": "Add or remove subscriptions from an existing co-term group.\n\nUse the `action` parameter to specify \"ADD\" or \"REMOVE\".\n",
        "operationId": "updateCotermGroup",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "cotermGroupId",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the co-term group.",
            "schema": {
              "type": "string",
              "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
            }
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateCoTermGroupRequest"
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
                  "$ref": "#/components/schemas/CoTermUpdateResponse"
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
      "UpdateCoTermGroupRequest": {
        "type": "object",
        "description": "Request body to add or remove subscriptions from a group.",
        "required": [
          "action",
          "subscriptions"
        ],
        "properties": {
          "action": {
            "type": "string",
            "description": "The operation to perform on the group.",
            "enum": [
              "ADD",
              "REMOVE"
            ],
            "example": "ADD"
          },
          "displayName": {
            "type": "string",
            "description": "Optional new name for the group.",
            "example": "Example co-term group name"
          },
          "preview": {
            "type": "boolean",
            "description": "If true, returns an estimate without executing changes.",
            "example": true
          },
          "prorate": {
            "type": "boolean",
            "description": "If true, executes changes immediately with proration.",
            "example": true
          },
          "subscriptions": {
            "type": "array",
            "description": "List of Subscription IDs to add or remove.",
            "items": {
              "type": "string",
              "description": "A subscription ID.",
              "example": "Fg2Hi3Jk4Lm5No6Pq7Rs8T"
            }
          }
        }
      },
      "CoTermUpdateResponse": {
        "type": "object",
        "description": "Response returned after updating a group (Add/Remove).",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.update"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "coTermChangesResult": {
            "type": "array",
            "description": "Results of the add/remove operation. Contains status or full preview details.",
            "items": {
              "$ref": "#/components/schemas/SubscriptionChangeResult"
            }
          }
        }
      },
      "SubscriptionChangeResult": {
        "type": "object",
        "description": "Polymorphic result. Contains 'status' for execution, or full plan details for preview.",
        "properties": {
          "subscription": {
            "type": "string",
            "description": "The subscription ID.",
            "example": "Fg2Hi3Jk4Lm5No6Pq7Rs8T"
          },
          "status": {
            "type": "string",
            "description": "Returned during execution or failure.",
            "example": "EXECUTED"
          },
          "error": {
            "type": "object",
            "description": "Error details if the operation failed for this subscription.",
            "properties": {
              "code": {
                "type": "string",
                "description": "Error code."
              },
              "message": {
                "type": "string",
                "description": "Error message."
              }
            }
          },
          "currentPlan": {
            "$ref": "#/components/schemas/PlanSnapshot"
          },
          "proposedPlan": {
            "$ref": "#/components/schemas/PlanSnapshot"
          },
          "amountDue": {
            "$ref": "#/components/schemas/AmountDue"
          },
          "discountTotals": {
            "$ref": "#/components/schemas/DiscountTotals"
          }
        }
      },
      "PlanSnapshot": {
        "type": "object",
        "description": "Snapshot of the pricing plan for estimation.",
        "properties": {
          "display": {
            "type": "string",
            "description": "Display name of the product/plan.",
            "example": "Furious Falcon Analytics"
          },
          "product": {
            "type": "string",
            "description": "Product path ID.",
            "example": "furious-falcon-analytics"
          },
          "billingFrequency": {
            "type": "string",
            "description": "Frequency of the billing cycle.",
            "example": "1 month"
          },
          "price": {
            "type": "number",
            "description": "Unit price of the product.",
            "example": 100
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted unit price.",
            "example": "$100.00"
          },
          "discount": {
            "type": "number",
            "description": "Discount amount applied.",
            "example": 0
          },
          "discountDisplay": {
            "type": "string",
            "description": "Formatted discount amount.",
            "example": "$0.00"
          },
          "quantity": {
            "type": "integer",
            "description": "Quantity of the product.",
            "example": 1
          },
          "subtotal": {
            "type": "number",
            "description": "Subtotal before tax.",
            "example": 100
          },
          "subtotalDisplay": {
            "type": "string",
            "description": "Formatted subtotal.",
            "example": "$100.00"
          },
          "tax": {
            "type": "number",
            "description": "Tax amount.",
            "example": 8
          },
          "taxDisplay": {
            "type": "string",
            "description": "Formatted tax amount.",
            "example": "$8.00"
          },
          "total": {
            "type": "number",
            "description": "Total amount including tax.",
            "example": 108
          },
          "totalDisplay": {
            "type": "string",
            "description": "Formatted total amount.",
            "example": "$108.00"
          },
          "taxPercent": {
            "type": "number",
            "description": "Tax percentage applied.",
            "example": 8
          },
          "taxPercentDisplay": {
            "type": "string",
            "description": "Formatted tax percentage.",
            "example": "8%"
          },
          "periodStartDate": {
            "type": "integer",
            "description": "Timestamp of plan period start.",
            "example": 1769990400000
          },
          "periodStartDateDisplay": {
            "type": "string",
            "description": "Display string for period start.",
            "example": "2/2/26"
          },
          "periodStartDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period start.",
            "example": "2026-02-02"
          },
          "periodEndDate": {
            "type": "integer",
            "description": "Timestamp of plan period end.",
            "example": 1769990400000
          },
          "periodEndDateDisplay": {
            "type": "string",
            "description": "Display string for period end.",
            "example": "2/2/26"
          },
          "periodEndDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for period end.",
            "example": "2026-02-02"
          },
          "prorationUtilizedDays": {
            "type": "integer",
            "description": "Number of days utilized in the proration.",
            "example": 0
          },
          "prorationRemainingDays": {
            "type": "integer",
            "description": "Number of days remaining in the proration.",
            "example": 28
          },
          "prorationTotalDays": {
            "type": "integer",
            "description": "Total days in the proration period.",
            "example": 28
          },
          "proratedItemCharge": {
            "type": "number",
            "description": "Charge amount for the prorated period.",
            "example": 103.57
          },
          "proratedItemChargeDisplay": {
            "type": "string",
            "description": "Formatted prorated charge.",
            "example": "$103.57"
          },
          "proratedItemCredit": {
            "type": "number",
            "description": "Credit amount for the prorated period.",
            "example": 100
          },
          "proratedItemCreditDisplay": {
            "type": "string",
            "description": "Formatted prorated credit.",
            "example": "$100.00"
          },
          "proratedItemSubtotal": {
            "type": "number",
            "description": "Subtotal of proration adjustments.",
            "example": 3.57
          },
          "proratedItemSubtotalDisplay": {
            "type": "string",
            "description": "Formatted proration subtotal.",
            "example": "$3.57"
          },
          "proratedItemTax": {
            "type": "number",
            "description": "Tax on the prorated amount.",
            "example": 0.28
          },
          "proratedItemTaxDisplay": {
            "type": "string",
            "description": "Formatted proration tax.",
            "example": "$0.28"
          },
          "proratedItemTotal": {
            "type": "number",
            "description": "Total prorated amount.",
            "example": 3.85
          },
          "proratedItemTotalDisplay": {
            "type": "string",
            "description": "Formatted total prorated amount.",
            "example": "$3.85"
          },
          "addons": {
            "type": "array",
            "description": "List of add-ons included in the plan.",
            "items": {
              "$ref": "#/components/schemas/AddonSnapshot"
            }
          },
          "subscriptionSubtotal": {
            "type": "number",
            "description": "Subscription subtotal.",
            "example": 100
          },
          "subscriptionSubtotalDisplay": {
            "type": "string",
            "description": "Formatted subscription subtotal.",
            "example": "$100.00"
          },
          "subscriptionTax": {
            "type": "number",
            "description": "Subscription tax.",
            "example": 8
          },
          "subscriptionTaxDisplay": {
            "type": "string",
            "description": "Formatted subscription tax.",
            "example": "$8.00"
          },
          "subscriptionTotal": {
            "type": "number",
            "description": "Subscription total.",
            "example": 108
          },
          "subscriptionTotalDisplay": {
            "type": "string",
            "description": "Formatted subscription total.",
            "example": "$108.00"
          },
          "subscriptionProratedCredit": {
            "type": "number",
            "description": "Credit amount for subscription proration.",
            "example": 100
          },
          "subscriptionProratedCreditDisplay": {
            "type": "string",
            "description": "Formatted credit amount.",
            "example": "$100.00"
          },
          "subscriptionProratedCharge": {
            "type": "number",
            "description": "Charge amount for subscription proration.",
            "example": 103.57
          },
          "subscriptionProratedChargeDisplay": {
            "type": "string",
            "description": "Formatted charge amount.",
            "example": "$103.57"
          }
        }
      },
      "AddonSnapshot": {
        "type": "object",
        "description": "Snapshot of an add-on product.",
        "properties": {
          "display": {
            "type": "string",
            "description": "Display name of the add-on.",
            "example": "Addon Name"
          },
          "product": {
            "type": "string",
            "description": "Product path ID of the add-on.",
            "example": "addon-product-path"
          },
          "priceDisplay": {
            "type": "string",
            "description": "Formatted price of the add-on.",
            "example": "$5.00"
          },
          "proratedItemChargeDisplay": {
            "type": "string",
            "description": "Formatted prorated charge for the add-on.",
            "example": "$2.50"
          }
        }
      },
      "AmountDue": {
        "type": "object",
        "description": "Summary of amounts due.",
        "properties": {
          "prorationSubtotal": {
            "type": "number",
            "description": "Subtotal of proration amounts.",
            "example": 3.57
          },
          "prorationSubtotalDisplay": {
            "type": "string",
            "description": "Formatted proration subtotal.",
            "example": "$3.57"
          },
          "prorationTax": {
            "type": "number",
            "description": "Tax on proration amounts.",
            "example": 0.28
          },
          "prorationTaxDisplay": {
            "type": "string",
            "description": "Formatted proration tax.",
            "example": "$0.28"
          },
          "totalAmountDue": {
            "type": "number",
            "description": "Total amount due now.",
            "example": 3.85
          },
          "totalAmountDueDisplay": {
            "type": "string",
            "description": "Formatted total amount due.",
            "example": "$3.85"
          },
          "nextChargeDate": {
            "type": "integer",
            "description": "Timestamp of the next charge.",
            "example": 1772496000000
          },
          "nextChargeDateDisplay": {
            "type": "string",
            "description": "Display string for next charge date.",
            "example": "3/3/26"
          },
          "nextChargeDateDisplayISO8601": {
            "type": "string",
            "description": "ISO8601 string for next charge date.",
            "example": "2026-03-03"
          },
          "nextChargeAmount": {
            "type": "number",
            "description": "Amount of the next charge.",
            "example": 108
          },
          "nextChargeAmountDisplay": {
            "type": "string",
            "description": "Formatted amount of the next charge.",
            "example": "$108.00"
          }
        }
      },
      "DiscountTotals": {
        "type": "object",
        "description": "Summary of discount totals.",
        "properties": {
          "productLevelDiscountTotal": {
            "type": "number",
            "description": "Total product-level discounts.",
            "example": 0
          },
          "productLevelDiscountTotalDisplay": {
            "type": "string",
            "description": "Formatted total product-level discounts.",
            "example": "$0.00"
          },
          "couponLevelDiscountTotal": {
            "type": "number",
            "description": "Total coupon-level discounts.",
            "example": 0
          },
          "couponLevelDiscountTotalDisplay": {
            "type": "string",
            "description": "Formatted total coupon-level discounts.",
            "example": "$0.00"
          }
        }
      }
    }
  }
}
```

Delete a co-term group

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Delete a co-term group

Dissolves the co-term group. 

Subscriptions are ungrouped and their status is set to "Opted Out". This does not cancel the subscriptions, only the grouping.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/coterm/{cotermGroupId}": {
      "delete": {
        "summary": "Delete a co-term group",
        "description": "Dissolves the co-term group. \n\nSubscriptions are ungrouped and their status is set to \"Opted Out\". This does not cancel the subscriptions, only the grouping.\n",
        "operationId": "deleteCotermGroup",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "cotermGroupId",
            "in": "path",
            "required": true,
            "description": "Unique identifier for the co-term group.",
            "schema": {
              "type": "string",
              "example": "X_yZ1a2Bc3De4Fg5Hi6JkL"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CoTermDeleteResponse"
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
      "CoTermDeleteResponse": {
        "type": "object",
        "description": "Response returned after deleting a co-term group.",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed.",
            "example": "subscriptions.coterm.delete"
          },
          "result": {
            "type": "string",
            "description": "The result of the operation.",
            "example": "success"
          },
          "subscriptions": {
            "type": "array",
            "description": "List of subscriptions that were ungrouped.",
            "items": {
              "type": "object",
              "properties": {
                "subscription": {
                  "type": "string",
                  "description": "The ID of the subscription.",
                  "example": "Ab1C-DeFgH-iJ2K3l4MmNo"
                },
                "status": {
                  "type": "string",
                  "description": "The new status of the subscription.",
                  "example": "Opted Out of Co-Terming"
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


Create a proforma invoice

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create a proforma invoice

Creates a new proforma invoice that reflects changes you want to make to a subscription.

# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/proformaInvoice": {
      "post": {
        "summary": "Create a proforma invoice",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "CreateProformaInvoice",
        "description": "Creates a new proforma invoice that reflects changes you want to make to a subscription.",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "subscription",
                  "product",
                  "prorate",
                  "quantity"
                ],
                "properties": {
                  "subscription": {
                    "type": "string",
                    "description": "Unique identifier for the subscription.",
                    "example": "1abc2DE_FGhIjKLm3NoPQR"
                  },
                  "product": {
                    "type": "string",
                    "description": "The product identifier.",
                    "example": "subscription1monthly"
                  },
                  "prorate": {
                    "type": "boolean",
                    "description": "Indicates whether the invoice should account for proration.\n\nIf you set prorate to `false`, FastSpring generates a proforma invoice that reflects changes that will take effect at the next renewal date.\n\nIf you set prorate to `true`, FastSpring generates a proforma invoice that reflects changes that will take effect immediately.\n\nNote: Prorated invoices are only valid for one day, as the amount adjusts daily based on the remaining time until the next renewal.\n",
                    "example": false
                  },
                  "quantity": {
                    "type": "integer",
                    "description": "The new quantity for the subscription.",
                    "example": 2
                  },
                  "language": {
                    "type": "string",
                    "description": "The language code for the response.",
                    "enum": [
                      "ar",
                      "cs",
                      "da",
                      "de",
                      "en",
                      "es",
                      "fi",
                      "fr",
                      "he",
                      "hr",
                      "it",
                      "ja",
                      "ko",
                      "nl",
                      "no",
                      "pl",
                      "pt",
                      "ru",
                      "sk",
                      "sv",
                      "tr",
                      "zh"
                    ],
                    "example": "es"
                  },
                  "pricing": {
                    "type": "object",
                    "properties": {
                      "price": {
                        "type": "object",
                        "properties": {
                          "USD": {
                            "type": "number",
                            "description": "The price in USD.",
                            "example": 200
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
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ProformaCreateInvoiceResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma400Response"
                }
              }
            }
          },
          "404": {
            "description": "Subscription Not Found",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma404Response"
                }
              }
            }
          },
          "405": {
            "description": "Method Not Allowed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma405Response"
                }
              }
            }
          },
          "500": {
            "description": "Internal Server Error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma500Response"
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
      "ProformaCreateInvoiceResponse": {
        "title": "ProformaInvoiceResponse",
        "type": "object",
        "properties": {
          "invoice": {
            "type": "object",
            "description": "Details of the generated proforma invoice.",
            "properties": {
              "id": {
                "type": "string",
                "description": "The invoice ID.",
                "example": "proforma12345"
              },
              "subscription": {
                "type": "string",
                "description": "The subscription ID.",
                "example": "aBCDE12fGH3iJkL4mNOpqr"
              },
              "product": {
                "type": "string",
                "description": "The product identifier.",
                "example": "subscription1monthly"
              },
              "quantity": {
                "type": "integer",
                "description": "The new quantity for the subscription.",
                "example": 2
              },
              "total_price": {
                "type": "object",
                "description": "Total price of the subscription.",
                "properties": {
                  "USD": {
                    "type": "number",
                    "description": "Price in USD.",
                    "example": 200
                  }
                }
              },
              "prorated": {
                "type": "boolean",
                "description": "Indicates if the invoice is prorated.",
                "example": false
              },
              "language": {
                "type": "string",
                "description": "Language of the proforma invoice.",
                "example": "es"
              },
              "created_at": {
                "type": "string",
                "format": "date-time",
                "description": "Timestamp when the invoice was created.",
                "example": "2025-01-01T12:34:56Z"
              }
            }
          },
          "proformaInvoiceHeader": {
            "type": "object",
            "description": "Header details of the proforma invoice.",
            "properties": {
              "currency": {
                "type": "string",
                "description": "Currency in which the proforma invoice was generated.",
                "example": "USD"
              },
              "timezone": {
                "type": "string",
                "description": "Timezone of the invoice generation.",
                "example": "UTC"
              },
              "language": {
                "type": "string",
                "description": "Language of the proforma invoice.",
                "example": "es"
              },
              "message": {
                "type": "string",
                "description": "Message regarding the validity of the invoice.",
                "example": "Esta factura y el monto en la proxima fecha de pago son validos siempre y cuando no haya cambios en el plan de suscripcion y/o los detalles de facturacion desde la fecha de hoy hasta la proxima fecha de cobro."
              },
              "logoUrl": {
                "type": "string",
                "description": "URL of the store logo, if available.",
                "example": "https://example.com/logo.png"
              },
              "proFormaInvoiceCreationDate": {
                "type": "integer",
                "description": "Creation date of the proforma invoice in epoch milliseconds.",
                "example": 1656016000000
              },
              "proFormaInvoiceCreationDateDisplay": {
                "type": "string",
                "description": "Creation date of the proforma invoice in human-readable format.",
                "example": "02/14/25"
              },
              "proFormaInvoiceCreationDateDisplayISO8601": {
                "type": "string",
                "description": "Creation date of the proforma invoice in ISO 8601 format.",
                "example": "2025-01-01"
              },
              "proFormaInvoiceExpirationDate": {
                "type": "integer",
                "description": "Expiration date of the proforma invoice in epoch milliseconds.",
                "example": 1658617600000
              },
              "proFormaInvoiceExpirationDateDisplay": {
                "type": "string",
                "description": "Expiration date of the proforma invoice in human-readable format.",
                "example": "02/14/25"
              },
              "proFormaInvoiceExpirationDateDisplayISO8601": {
                "type": "string",
                "description": "Expiration date of the proforma invoice in ISO 8601 format.",
                "example": "2025-02-14"
              }
            }
          },
          "supportContact": {
            "type": "object",
            "description": "Support contact details.",
            "properties": {
              "name": {
                "type": "string",
                "description": "Name of the support contact.",
                "example": "FastSpring Support"
              },
              "email": {
                "type": "string",
                "description": "Email address of the support contact.",
                "example": "support@fastspring.com"
              },
              "url": {
                "type": "string",
                "description": "Website of the support contact.",
                "example": "https://www.fastspring.com/support"
              }
            }
          },
          "merchantContact": {
            "type": "object",
            "description": "Merchant contact details.",
            "properties": {
              "merchantName": {
                "type": "string",
                "description": "Name of the merchant.",
                "example": "FastSpring"
              },
              "merchantAddress": {
                "type": "string",
                "description": "Address of the merchant.",
                "example": "11 W Victoria St Suite 207A Santa Barbara, CA 93101 US"
              }
            }
          },
          "coTerm": {
            "type": "object",
            "description": "Co-term data if applicable.",
            "properties": {
              "account": {
                "type": "string",
                "description": "Account associated with the co-term.",
                "example": "account123"
              },
              "coTermGroupId": {
                "type": "string",
                "description": "ID of the co-term group.",
                "example": "coTermGroup123"
              }
            }
          },
          "contact": {
            "type": "object",
            "description": "Customer contact details.",
            "properties": {
              "email": {
                "type": "string",
                "description": "Customer email.",
                "example": "customer@example.com"
              },
              "firstName": {
                "type": "string",
                "description": "Customer first name.",
                "example": "John"
              },
              "phone": {
                "type": "string",
                "description": "Customer phone number (optional).",
                "example": "123-456-7890"
              },
              "company": {
                "type": "string",
                "description": "Customer company (optional).",
                "example": "Customer Company"
              },
              "address": {
                "type": "object",
                "description": "Customer address.",
                "properties": {
                  "accountId": {
                    "type": "string",
                    "description": "Account ID of the customer.",
                    "example": "abCdE1FGH2Hij3KLMnOpqR"
                  },
                  "addressLine1": {
                    "type": "string",
                    "description": "First line of the address.",
                    "example": "123 Main St"
                  },
                  "city": {
                    "type": "string",
                    "description": "City of the address.",
                    "example": "Santa Barbara"
                  },
                  "region": {
                    "type": "string",
                    "description": "Region or state of the address.",
                    "example": "CA"
                  },
                  "postalCode": {
                    "type": "string",
                    "description": "Postal code of the address.",
                    "example": "12345"
                  },
                  "country": {
                    "type": "string",
                    "description": "Country of the address.",
                    "example": "US"
                  }
                }
              }
            }
          },
          "items": {
            "type": "object",
            "description": "Details of the items in the proforma invoice.",
            "properties": {
              "subscription": {
                "type": "string",
                "description": "Unique identifier for the subscription.",
                "example": "aBCDE12fGH3iJkL4mNOpqr"
              },
              "product": {
                "type": "string",
                "description": "The product identifier code.",
                "example": "subscription1monthly"
              },
              "quantity": {
                "type": "integer",
                "description": "The new quantity for the subscription.",
                "example": 2
              },
              "price": {
                "type": "object",
                "description": "Price details.",
                "properties": {
                  "USD": {
                    "type": "number",
                    "description": "Price in USD.",
                    "example": 200
                  }
                }
              }
            }
          }
        },
        "example": {
          "invoice": {
            "id": "proforma12345",
            "subscription": "aBCDE12fGH3iJkL4mNOpqr",
            "product": "subscription1monthly",
            "quantity": 2,
            "total_price": {
              "USD": 200
            },
            "prorated": false,
            "language": "es",
            "created_at": "2025-01-01T12:34:56Z"
          },
          "proformaInvoiceHeader": {
            "currency": "USD",
            "timezone": "UTC",
            "language": "es",
            "message": "Esta factura y el monto en la proxima fecha de pago son validos siempre y cuando no haya cambios en el plan de suscripcion y/o los detalles de facturacion desde la fecha de hoy hasta la proxima fecha de cobro.",
            "logoUrl": "https://example.com/logo.png",
            "proFormaInvoiceCreationDate": 1656016000000,
            "proFormaInvoiceCreationDateDisplay": "02/14/25",
            "proFormaInvoiceCreationDateDisplayISO8601": "2025-02-14",
            "proFormaInvoiceExpirationDate": 1658617600000,
            "proFormaInvoiceExpirationDateDisplay": "02/14/25",
            "proFormaInvoiceExpirationDateDisplayISO8601": "2025-02-14"
          },
          "supportContact": {
            "name": "FastSpring Support",
            "email": "support@fastspring.com",
            "url": "https://www.fastspring.com/support"
          },
          "merchantContact": {
            "merchantName": "FastSpring",
            "merchantAddress": "11 W Victoria St Suite 207A Santa Barbara, CA 93101 US"
          },
          "coTerm": {
            "account": "account123",
            "coTermGroupId": "coTermGroup123"
          },
          "contact": {
            "email": "customer@example.com",
            "firstName": "John",
            "phone": "123-456-7890",
            "company": "Customer Company",
            "address": {
              "accountId": "account123",
              "addressLine1": "123 Main St",
              "city": "Anytown",
              "region": "CA",
              "postalCode": "12345",
              "country": "US"
            }
          },
          "items": {
            "subscription": "aBCDE12fGH3iJkL4mNOpqr",
            "product": "subscription1monthly",
            "quantity": 2,
            "price": {
              "USD": 200
            }
          }
        }
      },
      "Proforma400Response": {
        "title": "BadRequestResponse",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "subscriptions.proforma.invoice must contain a single root object for the request. Please consult API documentation"
              }
            }
          }
        }
      },
      "Proforma404Response": {
        "title": "Proforma404Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Subscription ID does not exist: [subscription_id]"
              }
            }
          }
        }
      },
      "Proforma405Response": {
        "title": "Proforma405Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Subscription is in trial period: [subscription_id]"
              }
            }
          }
        }
      },
      "Proforma500Response": {
        "title": "Proforma500Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Exception getting subscription change estimate for sub_id:[subscription_id]"
              }
            }
          }
        }
      }
    }
  }
}
```

Retrieve a proforma invoice

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a proforma invoice

Retrieves a proforma invoice for the given `subscription_id`.

The invoice FastSpring returns provides an estimate of charges for the upcoming billing period, without taking into account any proposed changes to the plan.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/proformaInvoice/{subscription_id}": {
      "get": {
        "summary": "Retrieve a proforma invoice",
        "tags": [
          "Subscriptions"
        ],
        "operationId": "RetrieveProformaInvoice",
        "description": "Retrieves a proforma invoice for the given `subscription_id`.\n\nThe invoice FastSpring returns provides an estimate of charges for the upcoming billing period, without taking into account any proposed changes to the plan.\n",
        "parameters": [
          {
            "name": "subscription_id",
            "in": "path",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier for the subscription.",
            "example": "1abc2DE_FGhIjKLm3NoPQR"
          },
          {
            "name": "lang",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "enum": [
                "ar",
                "cs",
                "da",
                "de",
                "en",
                "es",
                "fi",
                "fr",
                "he",
                "hr",
                "it",
                "ja",
                "ko",
                "nl",
                "no",
                "pl",
                "pt",
                "ru",
                "sk",
                "sv",
                "tr",
                "zh"
              ],
              "example": "en"
            },
            "description": "Specify the language for the proforma invoice content. If not provided, defaults to the subscription's language or 'en'."
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ProformaInvoiceResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma400Response"
                }
              }
            }
          },
          "404": {
            "description": "Subscription Not Found",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma404Response"
                }
              }
            }
          },
          "405": {
            "description": "Method Not Allowed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma405Response"
                }
              }
            }
          },
          "500": {
            "description": "Internal Server Error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Proforma500Response"
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
      "ProformaInvoiceResponse": {
        "title": "ProformaInvoiceResponse",
        "type": "object",
        "properties": {
          "proformaInvoiceHeader": {
            "type": "object",
            "description": "Header details of the proforma invoice.",
            "properties": {
              "currency": {
                "type": "string",
                "description": "Currency in which the proforma invoice was generated."
              },
              "timezone": {
                "type": "string",
                "description": "Timezone of the invoice generation."
              },
              "language": {
                "type": "string",
                "description": "Language of the proforma invoice."
              },
              "message": {
                "type": "string",
                "description": "Message regarding the validity of the invoice."
              },
              "proFormaInvoiceCreationDate": {
                "type": "integer",
                "description": "Creation date of the proforma invoice in epoch milliseconds."
              },
              "proFormaInvoiceCreationDateDisplay": {
                "type": "string",
                "description": "Creation date of the proforma invoice in human-readable format."
              },
              "proFormaInvoiceCreationDateDisplayISO8601": {
                "type": "string",
                "description": "Creation date of the proforma invoice in ISO 8601 format."
              },
              "proFormaInvoiceExpirationDate": {
                "type": "integer",
                "description": "Expiration date of the proforma invoice in epoch milliseconds."
              },
              "proFormaInvoiceExpirationDateDisplay": {
                "type": "string",
                "description": "Expiration date of the proforma invoice in human-readable format."
              },
              "proFormaInvoiceExpirationDateDisplayISO8601": {
                "type": "string",
                "description": "Expiration date of the proforma invoice in ISO 8601 format."
              }
            }
          },
          "supportContact": {
            "type": "object",
            "description": "Support contact details.",
            "properties": {
              "name": {
                "type": "string",
                "description": "Name of the support contact."
              },
              "email": {
                "type": "string",
                "description": "Email address of the support contact."
              },
              "url": {
                "type": "string",
                "description": "Website of the support contact."
              }
            }
          },
          "merchantContact": {
            "type": "object",
            "description": "Merchant contact details.",
            "properties": {
              "merchantName": {
                "type": "string",
                "description": "Name of the merchant."
              },
              "merchantAddress": {
                "type": "string",
                "description": "Address of the merchant."
              }
            }
          },
          "contact": {
            "type": "object",
            "description": "Customer contact details.",
            "properties": {
              "email": {
                "type": "string",
                "description": "Email address of the customer."
              },
              "firstName": {
                "type": "string",
                "description": "First name of the customer."
              },
              "phone": {
                "type": "string",
                "description": "Phone number of the customer."
              },
              "company": {
                "type": "string",
                "description": "Company name of the customer."
              },
              "address": {
                "type": "object",
                "description": "Customer address.",
                "properties": {
                  "accountId": {
                    "type": "string",
                    "description": "Account ID of the customer."
                  },
                  "addressLine1": {
                    "type": "string",
                    "description": "First line of the address."
                  },
                  "city": {
                    "type": "string",
                    "description": "City of the address."
                  },
                  "region": {
                    "type": "string",
                    "description": "Region or state of the address."
                  },
                  "postalCode": {
                    "type": "string",
                    "description": "Postal code of the address."
                  },
                  "country": {
                    "type": "string",
                    "description": "Country of the address."
                  }
                }
              }
            }
          },
          "items": {
            "type": "object",
            "description": "Details of the items in the proforma invoice.",
            "properties": {
              "subscription": {
                "type": "string",
                "description": "Unique identifier for the subscription."
              },
              "currency": {
                "type": "string",
                "description": "Currency of the items."
              },
              "timezone": {
                "type": "string",
                "description": "Timezone of the items."
              },
              "periodStartDate": {
                "type": "integer",
                "description": "Start date of the period in epoch milliseconds."
              },
              "periodStartDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the start date."
              },
              "periodStartDateDisplayISO8601": {
                "type": "string",
                "description": "ISO 8601 format of the start date."
              },
              "periodEndDate": {
                "type": "integer",
                "description": "End date of the period in epoch milliseconds."
              },
              "periodEndDateDisplay": {
                "type": "string",
                "description": "Human-readable format of the end date."
              },
              "periodEndDateDisplayISO8601": {
                "type": "string",
                "description": "End date of the period in ISO 8601 format."
              },
              "endDate": {
                "type": "string",
                "description": "End date of the subscription."
              },
              "endDateDisplay": {
                "type": "string",
                "description": "End date of the subscription in human-readable format."
              },
              "endDateDisplayISO8601": {
                "type": "string",
                "description": "End date of the subscription in ISO 8601 format."
              },
              "remainingPeriods": {
                "type": "integer",
                "description": "Number of remaining periods."
              },
              "nextChargeDate": {
                "type": "integer",
                "description": "Next charge date in epoch milliseconds."
              },
              "nextChargeDateDisplay": {
                "type": "string",
                "description": "Next charge date in human-readable format."
              },
              "nextChargeDateDisplayISO8601": {
                "type": "string",
                "description": "Next charge date in ISO 8601 format."
              },
              "isProratable": {
                "type": "boolean",
                "description": "Indicates if the subscription is proratable."
              },
              "prorationStatus": {
                "type": "string",
                "description": "Proration status of the subscription."
              },
              "currentPlan": {
                "type": "object",
                "description": "Current plan details.",
                "properties": {
                  "display": {
                    "type": "string",
                    "description": "Display name of the current plan."
                  },
                  "product": {
                    "type": "string",
                    "description": "Product code of the current plan."
                  },
                  "billingFrequency": {
                    "type": "string",
                    "description": "Billing frequency of the current plan."
                  },
                  "price": {
                    "type": "number",
                    "description": "Price of the current plan."
                  },
                  "priceDisplay": {
                    "type": "string",
                    "description": "Price display of the current plan."
                  },
                  "discount": {
                    "type": "number",
                    "description": "Discount on the current plan."
                  },
                  "discountDisplay": {
                    "type": "string",
                    "description": "Discount display on the current plan."
                  },
                  "discounts": {
                    "type": "array",
                    "description": "List of discounts applied.",
                    "items": {
                      "type": "object",
                      "properties": {
                        "applyDiscountImmediately": {
                          "type": "boolean",
                          "description": "Indicates if the discount is applied immediately."
                        },
                        "discountPath": {
                          "type": "string",
                          "description": "Path of the discount."
                        },
                        "discountDuration": {
                          "type": "string",
                          "description": "Duration of the discount."
                        },
                        "percentValue": {
                          "type": "number",
                          "description": "Percentage value of the discount."
                        }
                      }
                    }
                  },
                  "quantity": {
                    "type": "integer",
                    "description": "Quantity of the current plan."
                  },
                  "subtotal": {
                    "type": "number",
                    "description": "Subtotal amount of the current plan."
                  },
                  "subtotalDisplay": {
                    "type": "string",
                    "description": "Subtotal display amount of the current plan."
                  },
                  "tax": {
                    "type": "number",
                    "description": "Tax amount of the current plan."
                  },
                  "taxDisplay": {
                    "type": "string",
                    "description": "Tax display amount of the current plan."
                  },
                  "total": {
                    "type": "number",
                    "description": "Total amount of the current plan."
                  },
                  "totalDisplay": {
                    "type": "string",
                    "description": "Total display amount of the current plan."
                  },
                  "taxPercent": {
                    "type": "number",
                    "description": "Tax percentage of the current plan."
                  },
                  "taxPercentDisplay": {
                    "type": "string",
                    "description": "Tax percentage display of the current plan."
                  },
                  "periodStartDate": {
                    "type": "integer",
                    "description": "Start date of the period in epoch milliseconds."
                  },
                  "periodStartDateDisplay": {
                    "type": "string",
                    "description": "Start date of the period in human-readable format."
                  },
                  "periodStartDateDisplayISO8601": {
                    "type": "string",
                    "description": "Start date of the period in ISO 8601 format."
                  },
                  "periodEndDate": {
                    "type": "integer",
                    "description": "End date of the period in epoch milliseconds."
                  },
                  "periodEndDateDisplay": {
                    "type": "string",
                    "description": "End date of the period in human-readable format."
                  },
                  "periodEndDateDisplayISO8601": {
                    "type": "string",
                    "description": "End date of the period in ISO 8601 format."
                  },
                  "prorationUtilizedDays": {
                    "type": "integer",
                    "description": "Number of utilized proration days."
                  },
                  "prorationRemainingDays": {
                    "type": "integer",
                    "description": "Number of remaining proration days."
                  },
                  "prorationTotalDays": {
                    "type": "integer",
                    "description": "Total number of proration days."
                  },
                  "proratedItemCharge": {
                    "type": "number",
                    "description": "Prorated item charge amount."
                  },
                  "proratedItemChargeDisplay": {
                    "type": "string",
                    "description": "Prorated item charge display amount."
                  },
                  "proratedItemCredit": {
                    "type": "number",
                    "description": "Prorated item credit amount."
                  },
                  "proratedItemCreditDisplay": {
                    "type": "string",
                    "description": "Prorated item credit display amount."
                  },
                  "proratedItemSubtotal": {
                    "type": "number",
                    "description": "Prorated item subtotal amount."
                  },
                  "proratedItemSubtotalDisplay": {
                    "type": "string",
                    "description": "Prorated item subtotal display amount."
                  },
                  "proratedItemTax": {
                    "type": "number",
                    "description": "Prorated item tax amount."
                  },
                  "proratedItemTaxDisplay": {
                    "type": "string",
                    "description": "Prorated item tax display amount."
                  },
                  "proratedItemTotal": {
                    "type": "number",
                    "description": "Prorated item total amount."
                  },
                  "proratedItemTotalDisplay": {
                    "type": "string",
                    "description": "Prorated item total display amount."
                  },
                  "addons": {
                    "type": "array",
                    "description": "List of add-ons.",
                    "items": {
                      "type": "object",
                      "properties": {
                        "display": {
                          "type": "string",
                          "description": "Display name of the add-on."
                        },
                        "product": {
                          "type": "string",
                          "description": "Product code of the add-on."
                        },
                        "price": {
                          "type": "number",
                          "description": "Price of the add-on."
                        },
                        "priceDisplay": {
                          "type": "string",
                          "description": "Price display of the add-on."
                        },
                        "discount": {
                          "type": "number",
                          "description": "Discount on the add-on."
                        },
                        "discountDisplay": {
                          "type": "string",
                          "description": "Discount display of the add-on."
                        },
                        "discounts": {
                          "type": "array",
                          "description": "List of discounts applied to the add-on.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "applyDiscountImmediately": {
                                "type": "boolean",
                                "description": "Indicates if the discount is applied immediately."
                              },
                              "discountPath": {
                                "type": "string",
                                "description": "Path of the discount."
                              },
                              "discountDuration": {
                                "type": "string",
                                "description": "Duration of the discount."
                              },
                              "percentValue": {
                                "type": "number",
                                "description": "Percentage value of the discount."
                              }
                            }
                          }
                        },
                        "quantity": {
                          "type": "integer",
                          "description": "Quantity of the add-on."
                        },
                        "subtotal": {
                          "type": "number",
                          "description": "Subtotal amount of the add-on."
                        },
                        "subtotalDisplay": {
                          "type": "string",
                          "description": "Subtotal display amount of the add-on."
                        },
                        "tax": {
                          "type": "number",
                          "description": "Tax amount of the add-on."
                        },
                        "taxDisplay": {
                          "type": "string",
                          "description": "Tax display amount of the add-on."
                        },
                        "total": {
                          "type": "number",
                          "description": "Total amount of the add-on."
                        },
                        "totalDisplay": {
                          "type": "string",
                          "description": "Total display amount of the add-on."
                        },
                        "taxPercent": {
                          "type": "number",
                          "description": "Tax percentage of the add-on."
                        },
                        "taxPercentDisplay": {
                          "type": "string",
                          "description": "Tax percentage display of the add-on."
                        },
                        "proratedItemCharge": {
                          "type": "number",
                          "description": "Prorated item charge amount."
                        },
                        "proratedItemChargeDisplay": {
                          "type": "string",
                          "description": "Prorated item charge display amount."
                        },
                        "proratedItemCredit": {
                          "type": "number",
                          "description": "Prorated item credit amount."
                        },
                        "proratedItemCreditDisplay": {
                          "type": "string",
                          "description": "Prorated item credit display amount."
                        },
                        "proratedItemSubtotal": {
                          "type": "number",
                          "description": "Prorated item subtotal amount."
                        },
                        "proratedItemSubtotalDisplay": {
                          "type": "string",
                          "description": "Prorated item subtotal display amount."
                        },
                        "proratedItemTax": {
                          "type": "number",
                          "description": "Prorated item tax amount."
                        },
                        "proratedItemTaxDisplay": {
                          "type": "string",
                          "description": "Prorated item tax display amount."
                        },
                        "proratedItemTotal": {
                          "type": "number",
                          "description": "Prorated item total amount."
                        },
                        "proratedItemTotalDisplay": {
                          "type": "string",
                          "description": "Prorated item total display amount."
                        }
                      }
                    }
                  },
                  "subscriptionSubtotal": {
                    "type": "number",
                    "description": "Subtotal amount of the subscription."
                  },
                  "subscriptionSubtotalDisplay": {
                    "type": "string",
                    "description": "Subtotal display amount of the subscription."
                  },
                  "subscriptionTax": {
                    "type": "number",
                    "description": "Tax amount of the subscription."
                  },
                  "subscriptionTaxDisplay": {
                    "type": "string",
                    "description": "Tax display amount of the subscription."
                  },
                  "subscriptionTotal": {
                    "type": "number",
                    "description": "Total amount of the subscription."
                  },
                  "subscriptionTotalDisplay": {
                    "type": "string",
                    "description": "Total display amount of the subscription."
                  },
                  "subscriptionProratedCharge": {
                    "type": "number",
                    "description": "Prorated charge amount of the subscription."
                  },
                  "subscriptionProratedChargeDisplay": {
                    "type": "string",
                    "description": "Prorated charge display amount of the subscription."
                  },
                  "subscriptionProratedCredit": {
                    "type": "number",
                    "description": "Prorated credit amount of the subscription."
                  },
                  "subscriptionProratedCreditDisplay": {
                    "type": "string",
                    "description": "Prorated credit display amount of the subscription."
                  }
                }
              },
              "proposedPlan": {
                "type": "object",
                "description": "Proposed plan details.",
                "properties": {
                  "display": {
                    "type": "string",
                    "description": "Display name of the proposed plan."
                  },
                  "product": {
                    "type": "string",
                    "description": "Product code of the proposed plan."
                  },
                  "billingFrequency": {
                    "type": "string",
                    "description": "Billing frequency of the proposed plan."
                  },
                  "price": {
                    "type": "number",
                    "description": "Price of the proposed plan."
                  },
                  "priceDisplay": {
                    "type": "string",
                    "description": "Price display of the proposed plan."
                  },
                  "discount": {
                    "type": "number",
                    "description": "Discount on the proposed plan."
                  },
                  "discountDisplay": {
                    "type": "string",
                    "description": "Discount display on the proposed plan."
                  },
                  "discounts": {
                    "type": "array",
                    "description": "List of discounts applied to the proposed plan.",
                    "items": {
                      "type": "object",
                      "properties": {
                        "applyDiscountImmediately": {
                          "type": "boolean",
                          "description": "Indicates if the discount is applied immediately."
                        },
                        "discountPath": {
                          "type": "string",
                          "description": "Path of the discount."
                        },
                        "discountDuration": {
                          "type": "string",
                          "description": "Duration of the discount."
                        },
                        "percentValue": {
                          "type": "number",
                          "description": "Percentage value of the discount."
                        }
                      }
                    }
                  },
                  "quantity": {
                    "type": "integer",
                    "description": "Quantity of the proposed plan."
                  },
                  "subtotal": {
                    "type": "number",
                    "description": "Subtotal amount of the proposed plan."
                  },
                  "subtotalDisplay": {
                    "type": "string",
                    "description": "Subtotal display amount of the proposed plan."
                  },
                  "tax": {
                    "type": "number",
                    "description": "Tax amount of the proposed plan."
                  },
                  "taxDisplay": {
                    "type": "string",
                    "description": "Tax display amount of the proposed plan."
                  },
                  "total": {
                    "type": "number",
                    "description": "Total amount of the proposed plan."
                  },
                  "totalDisplay": {
                    "type": "string",
                    "description": "Total display amount of the proposed plan."
                  },
                  "taxPercent": {
                    "type": "number",
                    "description": "Tax percentage of the proposed plan."
                  },
                  "taxPercentDisplay": {
                    "type": "string",
                    "description": "Tax percentage display of the proposed plan."
                  },
                  "periodStartDate": {
                    "type": "integer",
                    "description": "Start date of the period in epoch milliseconds."
                  },
                  "periodStartDateDisplay": {
                    "type": "string",
                    "description": "Start date of the period in human-readable format."
                  },
                  "periodStartDateDisplayISO8601": {
                    "type": "string",
                    "description": "Start date of the period in ISO 8601 format."
                  },
                  "periodEndDate": {
                    "type": "integer",
                    "description": "End date of the period in epoch milliseconds."
                  },
                  "periodEndDateDisplay": {
                    "type": "string",
                    "description": "End date of the period in human-readable format."
                  },
                  "periodEndDateDisplayISO8601": {
                    "type": "string",
                    "description": "End date of the period in ISO 8601 format."
                  },
                  "prorationUtilizedDays": {
                    "type": "integer",
                    "description": "Number of utilized proration days."
                  },
                  "prorationRemainingDays": {
                    "type": "integer",
                    "description": "Number of remaining proration days."
                  },
                  "prorationTotalDays": {
                    "type": "integer",
                    "description": "Total number of proration days."
                  },
                  "proratedItemCharge": {
                    "type": "number",
                    "description": "Prorated item charge amount."
                  },
                  "proratedItemChargeDisplay": {
                    "type": "string",
                    "description": "Prorated item charge display amount."
                  },
                  "proratedItemCredit": {
                    "type": "number",
                    "description": "Prorated item credit amount."
                  },
                  "proratedItemCreditDisplay": {
                    "type": "string",
                    "description": "Prorated item credit display amount."
                  },
                  "proratedItemSubtotal": {
                    "type": "number",
                    "description": "Prorated item subtotal amount."
                  },
                  "proratedItemSubtotalDisplay": {
                    "type": "string",
                    "description": "Prorated item subtotal display amount."
                  },
                  "proratedItemTax": {
                    "type": "number",
                    "description": "Prorated item tax amount."
                  },
                  "proratedItemTaxDisplay": {
                    "type": "string",
                    "description": "Prorated item tax display amount."
                  },
                  "proratedItemTotal": {
                    "type": "number",
                    "description": "Prorated item total amount."
                  },
                  "proratedItemTotalDisplay": {
                    "type": "string",
                    "description": "Prorated item total display amount."
                  },
                  "addons": {
                    "type": "array",
                    "description": "List of add-ons.",
                    "items": {
                      "type": "object",
                      "properties": {
                        "display": {
                          "type": "string",
                          "description": "Display name of the add-on."
                        },
                        "product": {
                          "type": "string",
                          "description": "Product code of the add-on."
                        },
                        "price": {
                          "type": "number",
                          "description": "Price of the add-on."
                        },
                        "priceDisplay": {
                          "type": "string",
                          "description": "Price display of the add-on."
                        },
                        "discount": {
                          "type": "number",
                          "description": "Discount on the add-on."
                        },
                        "discountDisplay": {
                          "type": "string",
                          "description": "Discount display of the add-on."
                        },
                        "discounts": {
                          "type": "array",
                          "description": "List of discounts applied to the add-on.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "applyDiscountImmediately": {
                                "type": "boolean",
                                "description": "Indicates if the discount is applied immediately."
                              },
                              "discountPath": {
                                "type": "string",
                                "description": "Path of the discount."
                              },
                              "discountDuration": {
                                "type": "string",
                                "description": "Duration of the discount."
                              },
                              "percentValue": {
                                "type": "number",
                                "description": "Percentage value of the discount."
                              }
                            }
                          }
                        },
                        "quantity": {
                          "type": "integer",
                          "description": "Quantity of the add-on."
                        },
                        "subtotal": {
                          "type": "number",
                          "description": "Subtotal amount of the add-on."
                        },
                        "subtotalDisplay": {
                          "type": "string",
                          "description": "Subtotal display amount of the add-on."
                        },
                        "tax": {
                          "type": "number",
                          "description": "Tax amount of the add-on."
                        },
                        "taxDisplay": {
                          "type": "string",
                          "description": "Tax display amount of the add-on."
                        },
                        "total": {
                          "type": "number",
                          "description": "Total amount of the add-on."
                        },
                        "totalDisplay": {
                          "type": "string",
                          "description": "Total display amount of the add-on."
                        },
                        "taxPercent": {
                          "type": "number",
                          "description": "Tax percentage of the add-on."
                        },
                        "taxPercentDisplay": {
                          "type": "string",
                          "description": "Tax percentage display of the add-on."
                        },
                        "proratedItemCharge": {
                          "type": "number",
                          "description": "Prorated item charge amount."
                        },
                        "proratedItemChargeDisplay": {
                          "type": "string",
                          "description": "Prorated item charge display amount."
                        },
                        "proratedItemCredit": {
                          "type": "number",
                          "description": "Prorated item credit amount."
                        },
                        "proratedItemCreditDisplay": {
                          "type": "string",
                          "description": "Prorated item credit display amount."
                        },
                        "proratedItemSubtotal": {
                          "type": "number",
                          "description": "Prorated item subtotal amount."
                        },
                        "proratedItemSubtotalDisplay": {
                          "type": "string",
                          "description": "Prorated item subtotal display amount."
                        },
                        "proratedItemTax": {
                          "type": "number",
                          "description": "Prorated item tax amount."
                        },
                        "proratedItemTaxDisplay": {
                          "type": "string",
                          "description": "Prorated item tax display amount."
                        },
                        "proratedItemTotal": {
                          "type": "number",
                          "description": "Prorated item total amount."
                        },
                        "proratedItemTotalDisplay": {
                          "type": "string",
                          "description": "Prorated item total display amount."
                        }
                      }
                    }
                  },
                  "subscriptionSubtotal": {
                    "type": "number",
                    "description": "Subtotal amount of the subscription."
                  },
                  "subscriptionSubtotalDisplay": {
                    "type": "string",
                    "description": "Subtotal display amount of the subscription."
                  },
                  "subscriptionTax": {
                    "type": "number",
                    "description": "Tax amount of the subscription."
                  },
                  "subscriptionTaxDisplay": {
                    "type": "string",
                    "description": "Tax display amount of the subscription."
                  },
                  "subscriptionTotal": {
                    "type": "number",
                    "description": "Total amount of the subscription."
                  },
                  "subscriptionTotalDisplay": {
                    "type": "string",
                    "description": "Total display amount of the subscription."
                  },
                  "subscriptionProratedCharge": {
                    "type": "number",
                    "description": "Prorated charge amount of the subscription."
                  },
                  "subscriptionProratedChargeDisplay": {
                    "type": "string",
                    "description": "Prorated charge display amount of the subscription."
                  },
                  "subscriptionProratedCredit": {
                    "type": "number",
                    "description": "Prorated credit amount of the subscription."
                  },
                  "subscriptionProratedCreditDisplay": {
                    "type": "string",
                    "description": "Prorated credit display amount of the subscription."
                  }
                }
              },
              "amountDue": {
                "type": "object",
                "description": "Details of the amount due.",
                "properties": {
                  "prorationSubtotal": {
                    "type": "number",
                    "description": "Proration subtotal amount."
                  },
                  "prorationSubtotalDisplay": {
                    "type": "string",
                    "description": "Proration subtotal display amount."
                  },
                  "prorationTax": {
                    "type": "number",
                    "description": "Proration tax amount."
                  },
                  "prorationTaxDisplay": {
                    "type": "string",
                    "description": "Proration tax display amount."
                  },
                  "totalAmountDue": {
                    "type": "number",
                    "description": "Total amount due."
                  },
                  "totalAmountDueDisplay": {
                    "type": "string",
                    "description": "Total amount due display."
                  },
                  "nextChargeDate": {
                    "type": "integer",
                    "description": "Next charge date in epoch milliseconds."
                  },
                  "nextChargeDateDisplay": {
                    "type": "string",
                    "description": "Next charge date in human-readable format."
                  },
                  "nextChargeDateDisplayISO8601": {
                    "type": "string",
                    "description": "Next charge date in ISO 8601 format."
                  },
                  "nextChargeAmount": {
                    "type": "number",
                    "description": "Next charge amount."
                  },
                  "nextChargeAmountDisplay": {
                    "type": "string",
                    "description": "Next charge amount display."
                  }
                }
              }
            }
          }
        },
        "example": {
          "proformaInvoiceHeader": {
            "currency": "USD",
            "timezone": "UTC",
            "language": "en",
            "message": "This invoice and the renewal amount on the next charge date are valid as long as there are no changes to the subscription plan or billing details from today's date until the next charge date.",
            "proFormaInvoiceCreationDate": 1719111203381,
            "proFormaInvoiceCreationDateDisplay": "02/14/25",
            "proFormaInvoiceCreationDateDisplayISO8601": "2025-01-01",
            "proFormaInvoiceExpirationDate": 1720569600000,
            "proFormaInvoiceExpirationDateDisplay": "02/14/2025",
            "proFormaInvoiceExpirationDateDisplayISO8601": "2025-02-14"
          },
          "supportContact": {
            "name": "My Tiered Pricing Model",
            "email": "john@fastspring.com",
            "url": "http://subscriptionmarketplace"
          },
          "merchantContact": {
            "merchantName": "FastSpring",
            "merchantAddress": "11 W Victoria St Suite 207A Santa Barbara, CA 93101 US"
          },
          "contact": {
            "email": "john@fastspring.com",
            "firstName": "John",
            "phone": "5555555555",
            "company": "FastSpring",
            "address": {
              "accountId": "Ip4hy3CCThi9Mnukvi3CAA",
              "addressLine1": null,
              "city": "Santa Barbara",
              "region": "US-CA",
              "postalCode": "93101",
              "country": "US"
            }
          },
          "items": {
            "subscription": "O99xUogETs-M7Ks28M4wuw",
            "currency": "USD",
            "timezone": "UTC",
            "periodStartDate": 1718064000000,
            "periodStartDateDisplay": "1/01/25",
            "periodStartDateDisplayISO8601": "2025-01-01",
            "periodEndDate": 1720569600000,
            "periodEndDateDisplay": "7/10/24",
            "periodEndDateDisplayISO8601": "2025-02-14",
            "endDate": null,
            "endDateDisplay": null,
            "endDateDisplayISO8601": null,
            "remainingPeriods": -1,
            "nextChargeDate": 1720656000000,
            "nextChargeDateDisplay": "01/08/25",
            "nextChargeDateDisplayISO8601": "2025-01-08",
            "isProratable": true,
            "prorationStatus": "Available",
            "currentPlan": {
              "display": "Pro",
              "product": "pro",
              "billingFrequency": "1 month",
              "price": 19,
              "priceDisplay": "$19.00",
              "discount": 5.28,
              "discountDisplay": "$5.28",
              "discounts": [
                {
                  "applyDiscountImmediately": true,
                  "discountPath": "pro",
                  "discountDuration": null,
                  "percentValue": 15
                },
                {
                  "applyDiscountImmediately": true,
                  "discountPath": "CYBER-SALE-SELECTED-PRODUCTS",
                  "discountDuration": null,
                  "percentValue": 15
                }
              ],
              "quantity": 1,
              "subtotal": 13.72,
              "subtotalDisplay": "$13.72",
              "tax": 0,
              "taxDisplay": "$0.00",
              "total": 13.72,
              "totalDisplay": "$13.72",
              "taxPercent": 0,
              "taxPercentDisplay": "0%",
              "periodStartDate": 1718064000000,
              "periodStartDateDisplay": "1/01/25",
              "periodStartDateDisplayISO8601": "2025-01-01",
              "periodEndDate": 1719100800000,
              "periodEndDateDisplay": "6/23/24",
              "periodEndDateDisplayISO8601": "2025-01-01",
              "prorationUtilizedDays": 12,
              "prorationRemainingDays": 18,
              "prorationTotalDays": 30,
              "proratedItemCharge": 8.23,
              "proratedItemChargeDisplay": "$8.23",
              "proratedItemCredit": 8.23,
              "proratedItemCreditDisplay": "$8.23",
              "proratedItemSubtotal": 0,
              "proratedItemSubtotalDisplay": "$0.00",
              "proratedItemTax": 0,
              "proratedItemTaxDisplay": "$0.00",
              "proratedItemTotal": 0,
              "proratedItemTotalDisplay": "$0.00",
              "addons": [
                {
                  "display": "Analytics Dashboard",
                  "product": "analytics-dashboard",
                  "price": 4,
                  "priceDisplay": "$4.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 4,
                  "subtotalDisplay": "$4.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 4,
                  "totalDisplay": "$4.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Marketing Automation",
                  "product": "marketing-automation",
                  "price": 7,
                  "priceDisplay": "$7.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 7,
                  "subtotalDisplay": "$7.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 7,
                  "totalDisplay": "$7.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Social Media Monitoring",
                  "product": "social-media-monitoring",
                  "price": 7,
                  "priceDisplay": "$7.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 7,
                  "subtotalDisplay": "$7.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 7,
                  "totalDisplay": "$7.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Email Integration",
                  "product": "email-integration",
                  "price": 5,
                  "priceDisplay": "$5.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 5,
                  "subtotalDisplay": "$5.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 5,
                  "totalDisplay": "$5.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                }
              ]
            },
            "proposedPlan": {
              "display": "Pro",
              "product": "pro",
              "billingFrequency": "1 month",
              "price": 19,
              "priceDisplay": "$19.00",
              "discount": 5.28,
              "discountDisplay": "$5.28",
              "discounts": [
                {
                  "applyDiscountImmediately": true,
                  "discountPath": "pro",
                  "discountDuration": null,
                  "percentValue": 15
                },
                {
                  "applyDiscountImmediately": true,
                  "discountPath": "CYBER-SALE-SELECTED-PRODUCTS",
                  "discountDuration": null,
                  "percentValue": 15
                }
              ],
              "quantity": 1,
              "subtotal": 13.72,
              "subtotalDisplay": "$13.72",
              "tax": 0,
              "taxDisplay": "$0.00",
              "total": 13.72,
              "totalDisplay": "$13.72",
              "taxPercent": 0,
              "taxPercentDisplay": "0%",
              "periodStartDate": 1719100800000,
              "periodStartDateDisplay": "6/23/24",
              "periodStartDateDisplayISO8601": "2025-01-01",
              "periodEndDate": 1720569600000,
              "periodEndDateDisplay": "7/10/24",
              "periodEndDateDisplayISO8601": "2025-02-14",
              "prorationUtilizedDays": 12,
              "prorationRemainingDays": 18,
              "prorationTotalDays": 30,
              "proratedItemCharge": 8.23,
              "proratedItemChargeDisplay": "$8.23",
              "proratedItemCredit": 8.23,
              "proratedItemCreditDisplay": "$8.23",
              "proratedItemSubtotal": 0,
              "proratedItemSubtotalDisplay": "$0.00",
              "proratedItemTax": 0,
              "proratedItemTaxDisplay": "$0.00",
              "proratedItemTotal": 0,
              "proratedItemTotalDisplay": "$0.00",
              "addons": [
                {
                  "display": "Analytics Dashboard",
                  "product": "analytics-dashboard",
                  "price": 4,
                  "priceDisplay": "$4.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 4,
                  "subtotalDisplay": "$4.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 4,
                  "totalDisplay": "$4.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Marketing Automation",
                  "product": "marketing-automation",
                  "price": 7,
                  "priceDisplay": "$7.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 7,
                  "subtotalDisplay": "$7.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 7,
                  "totalDisplay": "$7.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Social Media Monitoring",
                  "product": "social-media-monitoring",
                  "price": 7,
                  "priceDisplay": "$7.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 7,
                  "subtotalDisplay": "$7.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 7,
                  "totalDisplay": "$7.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                },
                {
                  "display": "Email Integration",
                  "product": "email-integration",
                  "price": 5,
                  "priceDisplay": "$5.00",
                  "discount": 0,
                  "discountDisplay": "$0.00",
                  "discounts": [],
                  "quantity": 1,
                  "subtotal": 5,
                  "subtotalDisplay": "$5.00",
                  "tax": 0,
                  "taxDisplay": "$0.00",
                  "total": 5,
                  "totalDisplay": "$5.00",
                  "taxPercent": 0,
                  "taxPercentDisplay": "0%",
                  "proratedItemCharge": 0,
                  "proratedItemChargeDisplay": "$0.00",
                  "proratedItemCredit": 0,
                  "proratedItemCreditDisplay": "$0.00",
                  "proratedItemSubtotal": 0,
                  "proratedItemSubtotalDisplay": "$0.00",
                  "proratedItemTax": 0,
                  "proratedItemTaxDisplay": "$0.00",
                  "proratedItemTotal": 0,
                  "proratedItemTotalDisplay": "$0.00"
                }
              ]
            }
          },
          "amountDue": {
            "prorationSubtotal": 0,
            "prorationSubtotalDisplay": "$0.00",
            "prorationTax": 0,
            "prorationTaxDisplay": "$0.00",
            "totalAmountDue": 0,
            "totalAmountDueDisplay": "$0.00",
            "nextChargeDate": 1720656000000,
            "nextChargeDateDisplay": "01/08/25",
            "nextChargeDateDisplayISO8601": "2025-01-08",
            "nextChargeAmount": 36.72,
            "nextChargeAmountDisplay": "$36.72"
          }
        }
      },
      "Proforma400Response": {
        "title": "BadRequestResponse",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "subscriptions.proforma.invoice must contain a single root object for the request. Please consult API documentation"
              }
            }
          }
        }
      },
      "Proforma404Response": {
        "title": "Proforma404Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Subscription ID does not exist: [subscription_id]"
              }
            }
          }
        }
      },
      "Proforma405Response": {
        "title": "Proforma405Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Subscription is in trial period: [subscription_id]"
              }
            }
          }
        }
      },
      "Proforma500Response": {
        "title": "Proforma500Response",
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "subscriptions.proforma.invoice"
          },
          "result": {
            "type": "string",
            "description": "The request outcome.",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "subscriptions.proforma.invoice": {
                "type": "string",
                "description": "Specific error message.",
                "example": "Exception getting subscription change estimate for sub_id:[subscription_id]"
              }
            }
          }
        }
      }
    }
  }
}
```

Configure cancel survey

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Configure cancel survey

Creates or updates a cancel survey for a subscription.

Either `reasonId` or `reasonName` can be provided, but `reasonId` takes precedence. 


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/cancelSurvey/response": {
      "post": {
        "summary": "Configure cancel survey",
        "description": "Creates or updates a cancel survey for a subscription.\n\nEither `reasonId` or `reasonName` can be provided, but `reasonId` takes precedence. \n",
        "operationId": "configureCancelSurvey",
        "tags": [
          "Subscriptions"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "subscription",
                  "cancelSurvey"
                ],
                "properties": {
                  "subscription": {
                    "type": "string",
                    "description": "Unique ID of the subscription for which the survey is submitted.",
                    "example": "aBCDE12fGH3iJkL4mNOpqr"
                  },
                  "cancelSurvey": {
                    "type": "object",
                    "required": [
                      "reasonId"
                    ],
                    "properties": {
                      "reasonId": {
                        "type": "string",
                        "enum": [
                          "1",
                          "2",
                          "3",
                          "4",
                          "5",
                          "6",
                          "7"
                        ],
                        "description": "ID of the cancel reason selected during cancellation.\n\n\nIf `reasonId` is provided, it overrides `reasonName`.\n\n\nEach ID corresponds to a predefined system reason name:\n  - 1 – cost\n  - 2 – need\n  - 3 – usability\n  - 4 – support\n  - 5 – integration\n  - 6 – alternative\n  - 7 – other\n",
                        "example": "7"
                      },
                      "reasonName": {
                        "type": "string",
                        "enum": [
                          "cost",
                          "need",
                          "usability",
                          "support",
                          "integration",
                          "alternative",
                          "other"
                        ],
                        "description": "Optional. Used only if `reasonId` is not provided. Must exactly match a valid reason name.\n",
                        "example": "other"
                      },
                      "feedbackText": {
                        "type": "string",
                        "description": "Optional freeform feedback submitted by the user.",
                        "example": "Optional feedback text"
                      },
                      "lang": {
                        "type": "string",
                        "enum": [
                          "ar",
                          "cs",
                          "da",
                          "de",
                          "es",
                          "fi",
                          "fr",
                          "hr",
                          "hu",
                          "it",
                          "iw",
                          "ja",
                          "ko",
                          "nl",
                          "no",
                          "pl",
                          "pt",
                          "ru",
                          "sk",
                          "sv",
                          "tr",
                          "zh"
                        ],
                        "description": "Language code used for localizing cancel reason fields.",
                        "example": "en"
                      }
                    }
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
                  "type": "object",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Unique identifier for the cancel survey record.",
                      "example": "2cDfgHIjKLmNOPqRs_tUV"
                    },
                    "reasonId": {
                      "type": "string",
                      "description": "The cancel reason ID that was saved.",
                      "example": "7"
                    },
                    "name": {
                      "type": "string",
                      "description": "Predefined name for the cancel reason.",
                      "example": "other"
                    },
                    "displayName": {
                      "type": "string",
                      "description": "Label shown to the user in the cancel survey.",
                      "example": "Other"
                    },
                    "description": {
                      "type": "string",
                      "description": "Long-form explanation of the cancel reason.",
                      "example": "Other"
                    },
                    "feedbackText": {
                      "type": "string",
                      "description": "User-submitted feedback captured during the cancellation.",
                      "example": "Optional feedback text"
                    },
                    "lang": {
                      "type": "string",
                      "description": "Language code used when the cancel survey was submitted.",
                      "example": "en"
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
    }
  }
}
```

Retrieve cancel survey

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve cancel survey

Retrieves the cancel survey state for a specific `subscriptionId`.
- If the subscription is **active**, returns the full list of cancel reasons for display.

- If the subscription is **already canceled**, returns the selected reason, feedback, and language used during cancellation.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/cancelSurvey/reasons/{subscriptionId}": {
      "get": {
        "summary": "Retrieve cancel survey",
        "description": "Retrieves the cancel survey state for a specific `subscriptionId`.\n- If the subscription is **active**, returns the full list of cancel reasons for display.\n\n- If the subscription is **already canceled**, returns the selected reason, feedback, and language used during cancellation.\n",
        "operationId": "retrieveCancelSurvey",
        "tags": [
          "Subscriptions"
        ],
        "parameters": [
          {
            "name": "subscriptionId",
            "in": "path",
            "required": true,
            "description": "Unique identifier of the subscription.",
            "schema": {
              "type": "string",
              "example": "1abc2DE_FGhIjKLm3NoPQR"
            }
          },
          {
            "$ref": "#/components/parameters/LangQueryParam"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "oneOf": [
                    {
                      "$ref": "#/components/schemas/CancelSurveyReasonsResponse"
                    },
                    {
                      "$ref": "#/components/schemas/CancelSurveyRecordedResponse"
                    }
                  ]
                },
                "examples": {
                  "NotCanceled": {
                    "summary": "Subscription not yet canceled",
                    "value": {
                      "language": "en",
                      "site": "ABC0DE1GHIJ2",
                      "subscription": {
                        "subscriptionId": "1abc2DE_FGhIjKLm3NoPQR",
                        "product": {
                          "path": "furious-falcon",
                          "displayName": "Furious Falcon"
                        }
                      },
                      "reasons": [
                        {
                          "id": "5",
                          "name": "integration",
                          "displayName": "Integration",
                          "enabled": true,
                          "description": "It doesn't work well with my existing tools or systems."
                        },
                        {
                          "id": "6",
                          "name": "alternative",
                          "displayName": "Alternative",
                          "enabled": true,
                          "description": "I switched to a different product or needed missing features."
                        },
                        {
                          "id": "7",
                          "name": "other",
                          "displayName": "Other",
                          "enabled": true,
                          "description": "Other"
                        }
                      ]
                    }
                  },
                  "AlreadyCanceled": {
                    "summary": "Subscription already canceled",
                    "value": {
                      "language": "en",
                      "site": "ABC0DE1GHIJ2",
                      "subscription": {
                        "subscriptionId": "1abc2DE_FGhIjKLm3NoPQR",
                        "product": {
                          "path": "fastspring-falcon",
                          "displayName": "FastSpring Falcon"
                        }
                      },
                      "cancelSurvey": {
                        "reasonId": "6",
                        "name": "alternative",
                        "displayName": "Alternative",
                        "description": "I switched to a different product or needed missing features.",
                        "feedbackText": "Switching to a competitor with better integration.",
                        "lang": "en"
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
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "parameters": {
      "LangQueryParam": {
        "name": "lang",
        "in": "query",
        "required": false,
        "description": "Language code for localization.",
        "schema": {
          "type": "string",
          "enum": [
            "ar",
            "cs",
            "da",
            "de",
            "es",
            "fi",
            "fr",
            "hr",
            "hu",
            "it",
            "iw",
            "ja",
            "ko",
            "nl",
            "no",
            "pl",
            "pt",
            "ru",
            "sk",
            "sv",
            "tr",
            "zh"
          ],
          "example": "en"
        }
      }
    },
    "schemas": {
      "CancelSurvey": {
        "title": "Cancel Survey Object",
        "type": "object",
        "description": "Survey data submitted during subscription cancellation.",
        "required": [
          "reasonId"
        ],
        "properties": {
          "reasonId": {
            "type": "string",
            "enum": [
              "1",
              "2",
              "3",
              "4",
              "5",
              "6",
              "7"
            ],
            "description": "Required. ID of the cancel reason. When present, overrides `reasonName`.\nMapped values: - 1 = cost - 2 = need - 3 = usability - 4 = support - 5 = integration - 6 = alternative - 7 = other\n",
            "example": "6"
          },
          "reasonName": {
            "type": "string",
            "enum": [
              "cost",
              "need",
              "usability",
              "support",
              "integration",
              "alternative",
              "other"
            ],
            "description": "Optional. Used only if `reasonId` is not supplied. Must exactly match a supported reason name.\n",
            "example": "alternative"
          },
          "displayName": {
            "type": "string",
            "description": "Display name of the cancel reason shown to users.",
            "example": "Alternative"
          },
          "description": {
            "type": "string",
            "description": "Long-form explanation of the cancel reason.",
            "example": "I switched to a different product or needed missing features."
          },
          "feedbackText": {
            "type": "string",
            "description": "Optional freeform feedback provided by the user.",
            "example": "It didn’t integrate well with our internal tooling."
          },
          "lang": {
            "type": "string",
            "enum": [
              "ar",
              "cs",
              "da",
              "de",
              "es",
              "fi",
              "fr",
              "hr",
              "hu",
              "it",
              "iw",
              "ja",
              "ko",
              "nl",
              "no",
              "pl",
              "pt",
              "ru",
              "sk",
              "sv",
              "tr",
              "zh"
            ],
            "description": "ISO 639-1 language code used for localization."
          }
        },
        "example": "en"
      },
      "CancelReason": {
        "type": "object",
        "description": "Metadata for cancel reasons displayed in the survey.",
        "properties": {
          "id": {
            "type": "string",
            "enum": [
              "1",
              "2",
              "3",
              "4",
              "5",
              "6",
              "7"
            ],
            "description": "Unique ID for the cancel reason.",
            "example": "6"
          },
          "name": {
            "type": "string",
            "enum": [
              "cost",
              "need",
              "usability",
              "support",
              "integration",
              "alternative",
              "other"
            ],
            "description": "Predefined name for the cancel reason.",
            "example": "alternative"
          },
          "displayName": {
            "type": "string",
            "description": "Label shown to users in the cancel survey UI.",
            "example": "Alternative"
          },
          "enabled": {
            "type": "boolean",
            "description": "Whether the reason is currently shown in the survey.",
            "example": true
          },
          "description": {
            "type": "string",
            "description": "Long-form explanation of the reason shown in the UI.",
            "example": "I switched to a different product or needed missing features."
          }
        }
      },
      "CancelSurveyReasonsResponse": {
        "title": "Cancel Survey Reasons Response",
        "type": "object",
        "description": "Response containing available cancel reasons for an active subscription.",
        "properties": {
          "language": {
            "type": "string",
            "description": "Language code used for localized reason fields.",
            "example": "en"
          },
          "site": {
            "type": "string",
            "description": "Site identifier for the subscription.",
            "example": "ABC0DE1GHIJ2"
          },
          "subscription": {
            "type": "object",
            "description": "Subscription and product metadata.",
            "properties": {
              "subscriptionId": {
                "type": "string",
                "description": "Unique identifier of the subscription.",
                "example": "1abc2DE_FGhIjKLm3NoPQR"
              },
              "product": {
                "type": "object",
                "properties": {
                  "path": {
                    "type": "string",
                    "description": "Unique identifier to reference a specific product, also known as the product path ID.",
                    "example": "furious-falcon"
                  },
                  "displayName": {
                    "type": "string",
                    "description": "Display name of the product.",
                    "example": "Furious Falcon"
                  }
                }
              }
            }
          },
          "reasons": {
            "type": "array",
            "description": "List of cancel reasons configured for this subscription.",
            "items": {
              "$ref": "#/components/schemas/CancelReason"
            }
          }
        }
      },
      "CancelSurveyRecordedResponse": {
        "title": "Cancel Survey Recorded Response",
        "type": "object",
        "description": "Response containing cancel survey details for a previously canceled subscription.",
        "properties": {
          "language": {
            "type": "string",
            "description": "Language code used when the cancel survey was submitted.",
            "example": "en"
          },
          "site": {
            "type": "string",
            "description": "Site identifier for the subscription.",
            "example": "ABC0DE1GHIJ2"
          },
          "subscription": {
            "type": "object",
            "description": "Subscription and product metadata.",
            "properties": {
              "subscriptionId": {
                "type": "string",
                "description": "Unique ID of the subscription.",
                "example": "1abc2DE_FGhIjKLm3NoPQR"
              },
              "product": {
                "type": "object",
                "properties": {
                  "path": {
                    "type": "string",
                    "description": "Unique identifier to reference a specific product, also known as the product path ID.",
                    "example": "fastspring-falcon"
                  },
                  "displayName": {
                    "type": "string",
                    "description": "Display name of the product.",
                    "example": "FastSpring Falcon"
                  }
                }
              }
            }
          },
          "cancelSurvey": {
            "$ref": "#/components/schemas/CancelSurvey"
          }
        }
      }
    }
  }
}
```

Query cancel survey responses

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Query cancel survey responses

Returns a paginated list of cancel survey responses submitted within a specific date range.

Results can be filtered by `startDate`, `endDate`, and optionally by `productPath`.

Supports sorting by `creationTime` or `reasonId`, in ascending or descending order.


# OpenAPI definition

```json
{
  "openapi": "3.0.1",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Subscriptions",
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
      "name": "Subscriptions",
      "description": "Manage and retrieve subscription details, including creation, updates, cancellations, and retrieving subscription statuses or related information.\n"
    }
  ],
  "paths": {
    "/subscriptions/cancelSurvey/response/query": {
      "post": {
        "summary": "Query cancel survey responses",
        "description": "Returns a paginated list of cancel survey responses submitted within a specific date range.\n\nResults can be filtered by `startDate`, `endDate`, and optionally by `productPath`.\n\nSupports sorting by `creationTime` or `reasonId`, in ascending or descending order.\n",
        "operationId": "queryCancelSurveyResponses",
        "tags": [
          "Subscriptions"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "startDate",
                  "endDate",
                  "pagination"
                ],
                "properties": {
                  "startDate": {
                    "type": "string",
                    "format": "date-time",
                    "description": "Start of the query window (ISO 8601 format).",
                    "example": "2025-01-01T00:00:00Z"
                  },
                  "endDate": {
                    "type": "string",
                    "format": "date-time",
                    "description": "End of the query window (ISO 8601 format).",
                    "example": "2026-01-31T23:59:59Z"
                  },
                  "productPath": {
                    "type": "string",
                    "description": "Optional filter to limit results to a specific product.",
                    "example": "my-product-path"
                  },
                  "pagination": {
                    "type": "object",
                    "required": [
                      "limit",
                      "offset"
                    ],
                    "properties": {
                      "limit": {
                        "type": "integer",
                        "description": "Max number of records to return per page.",
                        "example": 30
                      },
                      "offset": {
                        "type": "integer",
                        "description": "Number of records to skip before returning results.",
                        "example": 0
                      }
                    }
                  },
                  "sorting": {
                    "type": "object",
                    "properties": {
                      "field": {
                        "type": "string",
                        "enum": [
                          "creationTime",
                          "reasonId"
                        ],
                        "description": "Field to sort by. Options include:\n- `creationTime`: Time the survey was submitted\n- `reasonId`: Cancel reason ID\n",
                        "example": "creationTime"
                      },
                      "order": {
                        "type": "string",
                        "enum": [
                          "asc",
                          "desc"
                        ],
                        "description": "Sort direction.",
                        "example": "desc"
                      }
                    }
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
                  "type": "object",
                  "properties": {
                    "totalRecords": {
                      "type": "integer",
                      "description": "Total number of matching cancel survey records.",
                      "example": 3
                    },
                    "totalPages": {
                      "type": "integer",
                      "description": "Number of pages based on totalRecords and page size.",
                      "example": 1
                    },
                    "surveys": {
                      "type": "array",
                      "description": "List of cancel survey responses.",
                      "items": {
                        "type": "object",
                        "properties": {
                          "id": {
                            "type": "string",
                            "description": "Unique ID of the cancel survey response.",
                            "example": "2cDfgHIjKLmNOPqRs_tUV"
                          },
                          "subscriptionId": {
                            "type": "string",
                            "description": "ID of the subscription associated with the response.",
                            "example": "aBCDE12fGH3iJkL4mNOpqr"
                          },
                          "reasonId": {
                            "type": "string",
                            "description": "ID of the cancel reason.",
                            "example": "7"
                          },
                          "name": {
                            "type": "string",
                            "description": "Predefined name for the cancel reason.",
                            "example": "other"
                          },
                          "displayName": {
                            "type": "string",
                            "description": "Label shown to the user.",
                            "example": "Other"
                          },
                          "lang": {
                            "type": "string",
                            "description": "Language code used for the survey.",
                            "example": "en"
                          },
                          "description": {
                            "type": "string",
                            "description": "Long-form explanation of the cancel reason.",
                            "example": "Other"
                          },
                          "feedbackText": {
                            "type": "string",
                            "description": "Freeform feedback text submitted with the survey.",
                            "example": "We decided to switch to another tool that better meets our integration needs."
                          },
                          "site": {
                            "type": "string",
                            "description": "ID of the site associated with the subscription.",
                            "example": "ABC0DE1GHIJ2"
                          },
                          "productPath": {
                            "type": "string",
                            "description": "Product path of the canceled subscription.",
                            "example": "fastspring-falcon"
                          },
                          "creationTime": {
                            "type": "string",
                            "description": "Timestamp when the cancel survey response was created.",
                            "example": "Tue Mar 25 17:27:52 UTC 2025"
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
    }
  },
  "components": {
    "securitySchemes": {
      "auth": {
        "type": "http",
        "scheme": "basic"
      }
    }
  }
}
```
