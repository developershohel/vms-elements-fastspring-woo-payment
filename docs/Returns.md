Create a return

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create a return

Creates an order return request for full, partial, or combined refunds and returns processing details for each requested return.

Use the **Examples** dropdown in the **Try It!** panel to select a request body:

  | Example           | Description                                                                                         |
  |-------------------|-----------------------------------------------------------------------------------------------------|
  | **Full return**   | Submits a full refund for the specified order, returning 100% of its value without item‑level details. |
  | **Partial return**| Issues a partial refund for the specified order, refunding only the listed items and amounts.       |
  | **Combined return**| Mixes modes: one order is fully refunded, while another gets a partial refund on specified items.  |


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Returns",
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
      "name": "Returns",
      "description": "Creates an order return request for full, partial, or combined refunds and returns processing details for each requested return.\n"
    }
  ],
  "paths": {
    "/returns": {
      "post": {
        "summary": "Create a return",
        "tags": [
          "Returns"
        ],
        "operationId": "PostOneMoreOrdersReturns",
        "deprecated": false,
        "description": "Creates an order return request for full, partial, or combined refunds and returns processing details for each requested return.\n\nUse the **Examples** dropdown in the **Try It!** panel to select a request body:\n\n  | Example           | Description                                                                                         |\n  |-------------------|-----------------------------------------------------------------------------------------------------|\n  | **Full return**   | Submits a full refund for the specified order, returning 100% of its value without item‑level details. |\n  | **Partial return**| Issues a partial refund for the specified order, refunding only the listed items and amounts.       |\n  | **Combined return**| Mixes modes: one order is fully refunded, while another gets a partial refund on specified items.  |\n",
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/PostReturnsRequest"
              },
              "examples": {
                "fullRequestExample": {
                  "summary": "Full return",
                  "description": "Submits a full refund for the specified order, returning 100% of its value without item-level details.",
                  "value": {
                    "returns": [
                      {
                        "order": "abCdE1FGH2Hij3KLMnOpqR",
                        "reason": "DUPLICATE_ORDER",
                        "note": "As requested by customer",
                        "notification": "ORIGINAL",
                        "refundType": "FULL"
                      }
                    ]
                  }
                },
                "partialRequestExample": {
                  "summary": "Partial return",
                  "description": "Issues a partial refund for the specified order, refunding only the listed items and amounts.",
                  "value": {
                    "returns": [
                      {
                        "order": "abCdE1FGH2Hij3KLMnOpqR",
                        "reason": "DUPLICATE_ORDER",
                        "note": "As requested by customer",
                        "notification": "ORIGINAL",
                        "refundType": "PARTIAL",
                        "items": [
                          {
                            "product": "your-product-path-ID-1",
                            "amount": 15.75
                          }
                        ]
                      }
                    ]
                  }
                },
                "combinedRequestExample": {
                  "summary": "Combined return",
                  "description": "Demonstrates a mixed-mode return: one order is fully refunded, while a second order receives a partial refund on specified items.\n",
                  "value": {
                    "returns": [
                      {
                        "order": "1-abCdE1FGH2Hij3KLMnOp",
                        "reason": "DUPLICATE_ORDER",
                        "note": "As requested by customer",
                        "notification": "ORIGINAL",
                        "refundType": "FULL"
                      },
                      {
                        "order": "2-abCdE1FGH2Hij3KLMnOp",
                        "reason": "DUPLICATE_ORDER",
                        "note": "As requested by customer",
                        "notification": "ORIGINAL",
                        "refundType": "PARTIAL",
                        "items": [
                          {
                            "product": "your-product-path-ID-1",
                            "amount": 15.75
                          }
                        ]
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
                  "$ref": "#/components/schemas/PostReturnsResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PostReturns400ErrorResponse"
                },
                "examples": {
                  "400BadRequest": {
                    "summary": "400 Bad Request",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "product": "Product path is not found in the original order. Use a valid product path. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "refundTypeError": {
                    "summary": "REFUND_TYPE_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "REFUND_TYPE_ERROR": "Invalid refundType provided. Valid values are full or partial. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "itemsArrayError": {
                    "summary": "RETURN_ITEMS_ARRAY_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_ARRAY_ERROR": "Missing or invalid items field. Return items must be a non-empty array of objects. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "itemsArrayEmptyError": {
                    "summary": "RETURN_ITEMS_ARRAY_EMPTY_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_ARRAY_EMPTY_ERROR": "The items array cannot be empty. Add at least one return item object. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "itemsNotArrayError": {
                    "summary": "RETURN_ITEMS_NOT_AN_ARRAY_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_NOT_AN_ARRAY_ERROR": "The items field must be an array of objects. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "productNotSpecifiedError": {
                    "summary": "RETURN_ITEMS_PRODUCT_NOT_SPECIFIED_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_PRODUCT_NOT_SPECIFIED_ERROR": "Missing product path in return items. Each item must include a product path. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "productEmptyError": {
                    "summary": "RETURN_ITEMS_PRODUCT_EMPTY_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_PRODUCT_EMPTY_ERROR": "The product path cannot be empty. Provide a valid product identifier. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "productDuplicatedError": {
                    "summary": "RETURN_ITEMS_PRODUCT_DUPLICATED_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_PRODUCT_DUPLICATED_ERROR": "Duplicate product path in return items. Ensure each return item is unique. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "productNotFoundError": {
                    "summary": "RETURN_ITEMS_PRODUCT_NOT_FOUND_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_PRODUCT_NOT_FOUND_ERROR": "Product path is not found in the original order. Use a valid product path. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "amountNotSpecifiedError": {
                    "summary": "RETURN_ITEMS_AMOUNT_NOT_SPECIFIED_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_AMOUNT_NOT_SPECIFIED_ERROR": "Missing amount in return items. Specify a positive integer amount. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "amountZeroError": {
                    "summary": "RETURN_ITEMS_AMOUNT_ZERO_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_AMOUNT_ZERO_ERROR": "Invalid amount: must be greater than zero. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "amountTooLargeError": {
                    "summary": "RETURN_ITEMS_AMOUNT_GREATER_THAN_ORIGINAL_ITEM_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_AMOUNT_GREATER_THAN_ORIGINAL_ITEM_ERROR": "Invalid amount: exceeds original line amount. Use an amount ≤ the original line amount. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "currencyScaleMismatchError": {
                    "summary": "RETURN_ITEMS_AMOUNT_CURRENCY_SCALE_MISMATCH",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_AMOUNT_CURRENCY_SCALE_MISMATCH": "Invalid amount: decimals not allowed. Must match currency scale (0 decimal places). See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "elementsInvalidFormatError": {
                    "summary": "RETURN_ITEMS_ELEMENTS_INVALID_FORMAT",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_ELEMENTS_INVALID_FORMAT": "Invalid format in items element. Expecting { “product”: string, “amount”: integer }. See the Returns API reference for details."
                          }
                        }
                      ]
                    }
                  },
                  "noRemainingAmountError": {
                    "summary": "RETURN_ITEMS_NO_REMAINING_AMOUNT_ERROR",
                    "value": {
                      "returns": [
                        {
                          "action": "return.create",
                          "return": "AbC1D2eFGH34ijklmnopQrs",
                          "result": "error",
                          "error": {
                            "RETURN_ITEMS_NO_REMAINING_AMOUNT_ERROR": "No remaining amount to return for the item. All items have already been returned or refunded."
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
      "PostReturnsRequest": {
        "type": "object",
        "required": [
          "returns"
        ],
        "properties": {
          "returns": {
            "type": "array",
            "items": {
              "type": "object",
              "required": [
                "order"
              ],
              "properties": {
                "order": {
                  "type": "string",
                  "description": "Unique identifier for the order being returned or the order reference.",
                  "example": "abCdE1FGH2Hij3KLMnOpqR"
                },
                "reason": {
                  "type": "string",
                  "description": "Reason code for the return. The `reason` key in the request body accepts the following 10 unique values:\n\n| **Key**                      | **Reason**                                 |\n|------------------------------|--------------------------------------------|\n| `TAX_REFUND`                 | Tax return                                 |\n| `PRODUCT_NOT_RECEIVED`       | Product not received                       |\n| `PRODUCT_DIFFERENCE`         | Product not as expected                    |\n| `FRAUDULENT`                 | Fraudulent transaction                     |\n| `ORDER_ERROR`                | Incorrect order or order error             |\n| `DISCOUNT`                   | Discount or coupon                         |\n| `DUPLICATE_ORDER`            | Duplicate order                            |\n| `COMPATIBILITY_ISSUE`        | Compatibility issue                        |\n| `OTHER`                      | Other reason                               |\n| `NONE`                       | None                                       |\n\nIf your payload contains a value that doesn't match those in this table, FastSpring will still create the return but insert `None` as the reason for the return.\n",
                  "enum": [
                    "COMPATIBILITY_ISSUE",
                    "DISCOUNT",
                    "DUPLICATE_ORDER",
                    "FRAUDULENT",
                    "ORDER_ERROR",
                    "PRODUCT_DIFFERENCE",
                    "PRODUCT_NOT_RECEIVED",
                    "TAX_REFUND",
                    "OTHER",
                    "NONE"
                  ],
                  "example": "DUPLICATE_ORDER"
                },
                "note": {
                  "type": "string",
                  "description": "Optional, customer-visible note explaining the return reason.",
                  "example": "As requested by customer"
                },
                "notification": {
                  "type": "string",
                  "description": "Notify the customer of the return via email.\n\n- `ORIGINAL` = Yes, notify the customer.\n- `NONE` = No notification.\n",
                  "enum": [
                    "ORIGINAL",
                    "NONE"
                  ],
                  "example": "ORIGINAL"
                },
                "refundType": {
                  "type": "string",
                  "description": "Type of refund. Defaults to `FULL` if omitted.",
                  "enum": [
                    "FULL",
                    "PARTIAL"
                  ],
                  "example": "PARTIAL"
                },
                "items": {
                  "type": "array",
                  "description": "List of return items. Ignored when refundType = FULL.",
                  "minItems": 1,
                  "items": {
                    "type": "object",
                    "properties": {
                      "product": {
                        "type": "string",
                        "description": "Unique product path ID of the item.",
                        "example": "your-product-path-ID"
                      },
                      "amount": {
                        "type": "integer",
                        "description": "Amount for partial refunds.",
                        "example": 2.75
                      }
                    },
                    "required": [
                      "product"
                    ]
                  }
                }
              }
            }
          }
        }
      },
      "PostReturnsResponse": {
        "type": "object",
        "properties": {
          "returns": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "return": {
                  "type": "string",
                  "description": "Unique identifier to reference a specific return.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "quote": {
                  "type": "string",
                  "description": "Quote identifier associated with the return, if any.",
                  "nullable": true,
                  "example": null
                },
                "reference": {
                  "type": "string",
                  "description": "Reference code for the return.",
                  "example": "ABC1234567-8910-11121D"
                },
                "completed": {
                  "type": "boolean",
                  "description": "Indicates whether the return process has been completed.",
                  "example": true
                },
                "changed": {
                  "type": "number",
                  "description": "Timestamp indicating when the return was last changed.",
                  "example": 1731381500223
                },
                "changedValue": {
                  "type": "number",
                  "description": "Value representing when the return was changed.",
                  "example": 1731381500223
                },
                "changedInSeconds": {
                  "type": "number",
                  "description": "Timestamp in seconds indicating when the return was changed.",
                  "example": 1731381500
                },
                "changedDisplay": {
                  "type": "string",
                  "description": "Formatted display date for when the return was changed.",
                  "example": "11/12/24"
                },
                "changedDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO8601 formatted date for when the return was changed.",
                  "example": "2024-11-12"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the return is live.",
                  "example": false
                },
                "account": {
                  "type": "string",
                  "description": "Identifier for the account associated with the return.",
                  "example": "abCdE1FGH2Hij3KLMnOpqR"
                },
                "currency": {
                  "type": "string",
                  "description": "Currency of the return amount.",
                  "example": "USD"
                },
                "payoutCurrency": {
                  "type": "string",
                  "description": "Currency in which the payout is made.",
                  "example": "USD"
                },
                "totalReturn": {
                  "type": "number",
                  "description": "Total return amount.",
                  "example": 5
                },
                "totalReturnDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total return amount.",
                  "example": "$5.00"
                },
                "totalReturnInPayoutCurrency": {
                  "type": "number",
                  "description": "Total return amount in payout currency.",
                  "example": 5
                },
                "totalReturnInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total return amount in payout currency.",
                  "example": "$5.00"
                },
                "tax": {
                  "type": "number",
                  "description": "Tax amount on the return.",
                  "example": 0
                },
                "taxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the tax amount on the return.",
                  "example": "$0.00"
                },
                "taxInPayoutCurrency": {
                  "type": "number",
                  "description": "Tax amount in payout currency.",
                  "example": 0
                },
                "taxInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the tax amount in payout currency.",
                  "example": "$0.00"
                },
                "subtotal": {
                  "type": "number",
                  "description": "Subtotal amount of the return.",
                  "example": 5
                },
                "subtotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the subtotal amount.",
                  "example": "$5.00"
                },
                "subtotalInPayoutCurrency": {
                  "type": "number",
                  "description": "Subtotal amount in payout currency.",
                  "example": 5
                },
                "subtotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the subtotal amount in payout currency.",
                  "example": "$5.00"
                },
                "totalRefundInPayoutCurrency": {
                  "type": "number",
                  "description": "Total refund amount in payout currency.",
                  "example": 5
                },
                "payment": {
                  "type": "object",
                  "description": "Payment details associated with the return.",
                  "properties": {
                    "type": {
                      "type": "string",
                      "description": "Type of payment.",
                      "example": "test"
                    },
                    "cardEnding": {
                      "type": "string",
                      "description": "Last four digits of the card used for the payment.",
                      "example": "4242"
                    }
                  }
                },
                "reason": {
                  "type": "string",
                  "description": "Reason for the return.",
                  "example": "Duplicate Order"
                },
                "note": {
                  "type": "string",
                  "description": "Additional notes regarding the return.",
                  "example": "As requested by customer"
                },
                "type": {
                  "type": "string",
                  "description": "Type of the return.",
                  "example": "RETURN"
                },
                "refundPerformerType": {
                  "type": "string",
                  "description": "Type of performer handling the refund.",
                  "example": "api"
                },
                "refundSourceComponent": {
                  "type": "string",
                  "description": "Source component of the refund.",
                  "example": "refund"
                },
                "original": {
                  "type": "object",
                  "description": "Original order details associated with the return.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Unique identifier for the original order.",
                      "example": "AbC1D2eFGH34ijklmnopQrs"
                    },
                    "order": {
                      "type": "string",
                      "description": "Order identifier.",
                      "example": "AbC1D2eFGH34ijklmnopQrs"
                    },
                    "reference": {
                      "type": "string",
                      "description": "Reference code for the original order.",
                      "example": "ABC123456-7891-01112"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier associated with the original order.",
                      "example": "abCdE1FGH2Hij3KLMnOpqR"
                    },
                    "currency": {
                      "type": "string",
                      "description": "Currency of the original order amount.",
                      "example": "USD"
                    },
                    "payoutCurrency": {
                      "type": "string",
                      "description": "Currency in which the payout was made for the original order.",
                      "example": "USD"
                    },
                    "total": {
                      "type": "number",
                      "description": "Total amount of the original order.",
                      "example": 10
                    },
                    "totalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total amount of the original order.",
                      "example": "$10.00"
                    },
                    "totalInPayoutCurrency": {
                      "type": "number",
                      "description": "Total amount in payout currency of the original order.",
                      "example": 10
                    },
                    "totalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total amount in payout currency of the original order.",
                      "example": "$10.00"
                    },
                    "tax": {
                      "type": "number",
                      "description": "Tax amount on the original order.",
                      "example": 0
                    },
                    "taxDisplay": {
                      "type": "string",
                      "description": "Formatted display of the tax amount on the original order.",
                      "example": "$0.00"
                    },
                    "taxInPayoutCurrency": {
                      "type": "number",
                      "description": "Tax amount in payout currency for the original order.",
                      "example": 0
                    },
                    "taxInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the tax amount in payout currency for the original order.",
                      "example": "$0.00"
                    },
                    "subtotal": {
                      "type": "number",
                      "description": "Subtotal amount of the original order.",
                      "example": 10
                    },
                    "subtotalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal amount of the original order.",
                      "example": "$10.00"
                    },
                    "subtotalInPayoutCurrency": {
                      "type": "number",
                      "description": "Subtotal amount in payout currency of the original order.",
                      "example": 10
                    },
                    "subtotalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal amount in payout currency of the original order.",
                      "example": "$10.00"
                    },
                    "notes": {
                      "type": "array",
                      "description": "List of notes associated with the original order.",
                      "items": {
                        "type": "string"
                      },
                      "example": []
                    },
                    "tags": {
                      "type": "object",
                      "description": "Tags associated with the original order.",
                      "additionalProperties": {
                        "type": "string"
                      },
                      "example": {
                        "TagKey1": "test123",
                        "TagKey2": "TagValue2"
                      }
                    }
                  }
                },
                "customer": {
                  "type": "object",
                  "description": "Customer details associated with the return.",
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
                      "description": "Customer's email address.",
                      "example": "jane.doe@example.com"
                    },
                    "company": {
                      "type": "string",
                      "description": "Customer's company name, if any.",
                      "nullable": true,
                      "example": "Example Company"
                    },
                    "phone": {
                      "type": "string",
                      "description": "Customer's phone number.",
                      "example": "5555551234"
                    },
                    "subscribed": {
                      "type": "boolean",
                      "description": "Indicates if the customer is subscribed to marketing emails.",
                      "example": true
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
                        "description": "Product identifier.",
                        "example": "furious-falcon"
                      },
                      "quantity": {
                        "type": "number",
                        "description": "Quantity of the product returned.",
                        "example": 0
                      },
                      "display": {
                        "type": "string",
                        "description": "Display name of the product.",
                        "example": "Furious Falcon"
                      },
                      "sku": {
                        "type": "string",
                        "description": "SKU of the product.",
                        "nullable": true,
                        "example": "sku-12345"
                      },
                      "refundType": {
                        "type": "string",
                        "description": "Type of refund.",
                        "example": "Partial Refund"
                      },
                      "subtotal": {
                        "type": "number",
                        "description": "Subtotal amount for the item.",
                        "example": 5
                      },
                      "subtotalDisplay": {
                        "type": "string",
                        "description": "Formatted display of the subtotal amount.",
                        "example": "$5.00"
                      },
                      "subtotalInPayoutCurrency": {
                        "type": "number",
                        "description": "Subtotal amount in payout currency.",
                        "example": 5
                      },
                      "subtotalInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Formatted display of the subtotal amount in payout currency.",
                        "example": "$5.00"
                      },
                      "attributes": {
                        "type": "object",
                        "description": "Attributes associated with the item.",
                        "additionalProperties": {
                          "type": "string"
                        },
                        "example": {
                          "AttributeKey1": "AttributeValue1",
                          "AttributeKey2": "AttributeValue2"
                        }
                      },
                      "withholdings": {
                        "type": "object",
                        "description": "Withholdings applied to the item.",
                        "properties": {
                          "taxWithholdings": {
                            "type": "boolean",
                            "description": "Indicates if tax is withheld for the item.",
                            "example": false
                          }
                        }
                      }
                    }
                  }
                },
                "refundPerformer": {
                  "type": "string",
                  "description": "Identifier of the performer handling the refund.",
                  "example": "jane.doe@example.com"
                },
                "action": {
                  "type": "string",
                  "description": "Action taken for the refund.",
                  "example": "return.create"
                },
                "result": {
                  "type": "string",
                  "description": "Result of the refund action.",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "PostReturns400ErrorResponse": {
        "type": "object",
        "properties": {
          "returns": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed on the return.",
                  "example": "return.create"
                },
                "return": {
                  "type": "string",
                  "description": "Unique identifier for the return attempt.",
                  "example": "AbC1D2eFGH34ijklmnopQrs"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "description": "Map of error codes to human-readable messages.",
                  "properties": {
                    "product": {
                      "type": "string",
                      "description": "Error message related to the order return request.",
                      "example": "Product path is not found in the original order. Use a valid product path. See the Returns API reference for details."
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

Retrieve a return

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a return

Retrieves the details of one or more returns.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Returns",
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
      "name": "Returns",
      "description": "Creates an order return request for full, partial, or combined refunds and returns processing details for each requested return.\n"
    }
  ],
  "paths": {
    "/returns/{return_id}": {
      "get": {
        "summary": "Retrieve a return",
        "tags": [
          "Returns"
        ],
        "operationId": "Getoneormultiplereturns",
        "deprecated": false,
        "description": "Retrieves the details of one or more returns.",
        "parameters": [
          {
            "name": "return_id",
            "in": "path",
            "required": true,
            "description": "Unique identifier to reference a specific return.",
            "example": "aBCDE12fGH3iJkL4mNOpqr",
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
                  "$ref": "#/components/schemas/GetReturnsResponse"
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
      "GetReturnsResponse": {
        "type": "object",
        "properties": {
          "returns": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "return": {
                  "type": "string",
                  "description": "Unique identifier to reference a specific return.",
                  "example": "aBCDE12fGH3iJkL4mNOpqr"
                },
                "quote": {
                  "type": "string",
                  "description": "Quote identifier associated with the return, if any.",
                  "nullable": true,
                  "example": null
                },
                "reference": {
                  "type": "string",
                  "description": "Reference code for the return.",
                  "example": "ABC1234567-8910-11121D"
                },
                "completed": {
                  "type": "boolean",
                  "description": "Indicates whether the return process has been completed.",
                  "example": true
                },
                "changed": {
                  "type": "number",
                  "description": "Timestamp indicating when the return was last changed.",
                  "example": 1731381500223
                },
                "changedValue": {
                  "type": "number",
                  "description": "Value representing when the return was changed.",
                  "example": 1731381500223
                },
                "changedInSeconds": {
                  "type": "number",
                  "description": "Timestamp in seconds indicating when the return was changed.",
                  "example": 1731381500
                },
                "changedDisplay": {
                  "type": "string",
                  "description": "Formatted display date for when the return was changed.",
                  "example": "11/12/24"
                },
                "changedDisplayISO8601": {
                  "type": "string",
                  "format": "date",
                  "description": "ISO8601 formatted date for when the return was changed.",
                  "example": "2024-11-12"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the return is live.",
                  "example": false
                },
                "account": {
                  "type": "string",
                  "description": "Identifier for the account associated with the return.",
                  "example": "abCdE1FGH2Hij3KLMnOpqR"
                },
                "currency": {
                  "type": "string",
                  "description": "Currency of the return amount.",
                  "example": "USD"
                },
                "payoutCurrency": {
                  "type": "string",
                  "description": "Currency in which the payout is made.",
                  "example": "USD"
                },
                "totalReturn": {
                  "type": "number",
                  "description": "Total return amount.",
                  "example": 5
                },
                "totalReturnDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total return amount.",
                  "example": "$5.00"
                },
                "totalReturnInPayoutCurrency": {
                  "type": "number",
                  "description": "Total return amount in payout currency.",
                  "example": 5
                },
                "totalReturnInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the total return amount in payout currency.",
                  "example": "$5.00"
                },
                "tax": {
                  "type": "number",
                  "description": "Tax amount on the return.",
                  "example": 0
                },
                "taxDisplay": {
                  "type": "string",
                  "description": "Formatted display of the tax amount on the return.",
                  "example": "$0.00"
                },
                "taxInPayoutCurrency": {
                  "type": "number",
                  "description": "Tax amount in payout currency.",
                  "example": 0
                },
                "taxInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the tax amount in payout currency.",
                  "example": "$0.00"
                },
                "subtotal": {
                  "type": "number",
                  "description": "Subtotal amount of the return.",
                  "example": 5
                },
                "subtotalDisplay": {
                  "type": "string",
                  "description": "Formatted display of the subtotal amount.",
                  "example": "$5.00"
                },
                "subtotalInPayoutCurrency": {
                  "type": "number",
                  "description": "Subtotal amount in payout currency.",
                  "example": 5
                },
                "subtotalInPayoutCurrencyDisplay": {
                  "type": "string",
                  "description": "Formatted display of the subtotal amount in payout currency.",
                  "example": "$5.00"
                },
                "totalRefundInPayoutCurrency": {
                  "type": "number",
                  "description": "Total refund amount in payout currency.",
                  "example": 5
                },
                "payment": {
                  "type": "object",
                  "description": "Payment details associated with the return.",
                  "properties": {
                    "type": {
                      "type": "string",
                      "description": "Type of payment.",
                      "example": "test"
                    },
                    "cardEnding": {
                      "type": "string",
                      "description": "Last four digits of the card used for the payment.",
                      "example": "4242"
                    }
                  }
                },
                "reason": {
                  "type": "string",
                  "description": "Reason for the return.",
                  "example": "Duplicate Order"
                },
                "note": {
                  "type": "string",
                  "description": "Additional notes regarding the return.",
                  "example": "As requested by customer"
                },
                "type": {
                  "type": "string",
                  "description": "Type of the return.",
                  "example": "RETURN"
                },
                "refundPerformerType": {
                  "type": "string",
                  "description": "Type of performer handling the refund.",
                  "example": "api"
                },
                "refundSourceComponent": {
                  "type": "string",
                  "description": "Source component of the refund.",
                  "example": "refund"
                },
                "original": {
                  "type": "object",
                  "description": "Original order details associated with the return.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Unique identifier for the original order.",
                      "example": "AbC1D2eFGH34ijklmnopQrs"
                    },
                    "order": {
                      "type": "string",
                      "description": "Order identifier.",
                      "example": "AbC1D2eFGH34ijklmnopQrs"
                    },
                    "reference": {
                      "type": "string",
                      "description": "Reference code for the original order.",
                      "example": "ABC123456-7891-01112"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier associated with the original order.",
                      "example": "abCdE1FGH2Hij3KLMnOpqR"
                    },
                    "currency": {
                      "type": "string",
                      "description": "Currency of the original order amount.",
                      "example": "USD"
                    },
                    "payoutCurrency": {
                      "type": "string",
                      "description": "Currency in which the payout was made for the original order.",
                      "example": "USD"
                    },
                    "total": {
                      "type": "number",
                      "description": "Total amount of the original order.",
                      "example": 10
                    },
                    "totalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total amount of the original order.",
                      "example": "$10.00"
                    },
                    "totalInPayoutCurrency": {
                      "type": "number",
                      "description": "Total amount in payout currency of the original order.",
                      "example": 10
                    },
                    "totalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the total amount in payout currency of the original order.",
                      "example": "$10.00"
                    },
                    "tax": {
                      "type": "number",
                      "description": "Tax amount on the original order.",
                      "example": 0
                    },
                    "taxDisplay": {
                      "type": "string",
                      "description": "Formatted display of the tax amount on the original order.",
                      "example": "$0.00"
                    },
                    "taxInPayoutCurrency": {
                      "type": "number",
                      "description": "Tax amount in payout currency for the original order.",
                      "example": 0
                    },
                    "taxInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the tax amount in payout currency for the original order.",
                      "example": "$0.00"
                    },
                    "subtotal": {
                      "type": "number",
                      "description": "Subtotal amount of the original order.",
                      "example": 10
                    },
                    "subtotalDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal amount of the original order.",
                      "example": "$10.00"
                    },
                    "subtotalInPayoutCurrency": {
                      "type": "number",
                      "description": "Subtotal amount in payout currency of the original order.",
                      "example": 10
                    },
                    "subtotalInPayoutCurrencyDisplay": {
                      "type": "string",
                      "description": "Formatted display of the subtotal amount in payout currency of the original order.",
                      "example": "$10.00"
                    },
                    "notes": {
                      "type": "array",
                      "description": "List of notes associated with the original order.",
                      "items": {
                        "type": "string"
                      },
                      "example": []
                    },
                    "tags": {
                      "type": "object",
                      "description": "Tags associated with the original order.",
                      "additionalProperties": {
                        "type": "string"
                      },
                      "example": {
                        "TagKey1": "test123",
                        "TagKey2": "TagValue2"
                      }
                    }
                  }
                },
                "customer": {
                  "type": "object",
                  "description": "Customer details associated with the return.",
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
                      "description": "Customer's email address.",
                      "example": "jane.doe@example.com"
                    },
                    "company": {
                      "type": "string",
                      "description": "Customer's company name, if any.",
                      "nullable": true,
                      "example": "Example Company"
                    },
                    "phone": {
                      "type": "string",
                      "description": "Customer's phone number.",
                      "example": "5555551234"
                    },
                    "subscribed": {
                      "type": "boolean",
                      "description": "Indicates if the customer is subscribed.",
                      "example": true
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
                        "description": "Product identifier.",
                        "example": "furious-falcon"
                      },
                      "quantity": {
                        "type": "number",
                        "description": "Quantity of the product returned.",
                        "example": 0
                      },
                      "display": {
                        "type": "string",
                        "description": "Display name of the product.",
                        "example": "Furious Falcon"
                      },
                      "sku": {
                        "type": "string",
                        "description": "SKU of the product.",
                        "nullable": true,
                        "example": "sku-12345"
                      },
                      "refundType": {
                        "type": "string",
                        "description": "Type of refund (e.g., Partial Refund).",
                        "example": "Partial Refund"
                      },
                      "subtotal": {
                        "type": "number",
                        "description": "Subtotal amount for the item.",
                        "example": 5
                      },
                      "subtotalDisplay": {
                        "type": "string",
                        "description": "Formatted display of the subtotal amount.",
                        "example": "$5.00"
                      },
                      "subtotalInPayoutCurrency": {
                        "type": "number",
                        "description": "Subtotal amount in payout currency.",
                        "example": 5
                      },
                      "subtotalInPayoutCurrencyDisplay": {
                        "type": "string",
                        "description": "Formatted display of the subtotal amount in payout currency.",
                        "example": "$5.00"
                      },
                      "attributes": {
                        "type": "object",
                        "description": "Attributes associated with the item.",
                        "additionalProperties": {
                          "type": "string"
                        },
                        "example": {
                          "AttributeKey1": "AttributeValue1",
                          "AttributeKey2": "AttributeValue2"
                        }
                      },
                      "withholdings": {
                        "type": "object",
                        "description": "Withholdings applied to the item.",
                        "properties": {
                          "taxWithholdings": {
                            "type": "boolean",
                            "description": "Indicates if tax is withheld for the item.",
                            "example": false
                          }
                        }
                      }
                    }
                  }
                },
                "refundPerformer": {
                  "type": "string",
                  "description": "Identifier of the performer handling the refund.",
                  "example": "jane.doe@example.com"
                },
                "action": {
                  "type": "string",
                  "description": "Action taken for the refund.",
                  "example": "return.get"
                },
                "result": {
                  "type": "string",
                  "description": "Result of the refund action.",
                  "example": "success"
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