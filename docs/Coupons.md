List all coupons

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all coupons

Returns an array of all coupon path identifiers.

**Note:** Returns path identifiers only — not full coupon objects.
Use `GET /coupons/{coupon_id}` to retrieve full details for a specific
coupon. Expansion is not supported on this endpoint.


# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons": {
      "get": {
        "tags": [
          "Coupons"
        ],
        "summary": "List all coupons",
        "description": "Returns an array of all coupon path identifiers.\n\n**Note:** Returns path identifiers only — not full coupon objects.\nUse `GET /coupons/{coupon_id}` to retrieve full details for a specific\ncoupon. Expansion is not supported on this endpoint.\n",
        "operationId": "listCoupons",
        "responses": {
          "200": {
            "description": "Successful response.",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "coupons": {
                      "type": "array",
                      "description": "Array of coupon path identifiers.",
                      "items": {
                        "type": "string"
                      },
                      "example": [
                        "summer-sale-2026",
                        "new-subscriber-offer",
                        "annual-upgrade-promo"
                      ]
                    }
                  }
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/ValidationError"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "responses": {
      "ValidationError": {
        "description": "Validation or request error. Returns `200 OK` — always check the `result` field rather than relying on the HTTP status code alone.",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/ErrorResponse"
            }
          }
        }
      }
    },
    "schemas": {
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      }
    }
  }
}
```

Create or update a coupon

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create or update a coupon

Creates a new coupon or updates an existing one. The operation is
determined automatically based on whether the `coupon` path identifier
already exists — no separate update endpoint is required.

The `action` field in the response confirms which operation was
performed: `coupon.create` or `coupon.update`.

**Warning:** Including the `codes` array in an update request
permanently replaces all existing codes. Omit the `codes` field to
preserve existing codes.


# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons": {
      "post": {
        "tags": [
          "Coupons"
        ],
        "summary": "Create or update a coupon",
        "description": "Creates a new coupon or updates an existing one. The operation is\ndetermined automatically based on whether the `coupon` path identifier\nalready exists — no separate update endpoint is required.\n\nThe `action` field in the response confirms which operation was\nperformed: `coupon.create` or `coupon.update`.\n\n**Warning:** Including the `codes` array in an update request\npermanently replaces all existing codes. Omit the `codes` field to\npreserve existing codes.\n",
        "operationId": "upsertCoupon",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CouponRequest"
              },
              "examples": {
                "createOrderLevelFlatDiscount": {
                  "summary": "Order-level flat discount (Beta)",
                  "value": {
                    "coupon": "summer-sale-2026",
                    "orderLevelDiscount": true,
                    "discount": {
                      "type": "flat",
                      "amount": {
                        "USD": 10,
                        "EUR": 9,
                        "GBP": 8
                      }
                    },
                    "reason": {
                      "en": "Summer Sale — $10 off your order"
                    },
                    "limit": 500,
                    "available": {
                      "start": "2026-06-01",
                      "end": "2026-08-31"
                    },
                    "codes": [
                      "SUMMER10"
                    ]
                  }
                },
                "createItemLevelPercentDiscount": {
                  "summary": "Item-level percent discount",
                  "value": {
                    "coupon": "new-subscriber-offer",
                    "orderLevelDiscount": false,
                    "discount": {
                      "type": "percent",
                      "percent": 50
                    },
                    "discountPeriodCount": 3,
                    "applyDiscountImmediately": true,
                    "combine": false,
                    "reason": {
                      "en": "50% off for your first 3 months"
                    },
                    "products": [
                      "pro-plan-monthly",
                      "pro-plan-annual"
                    ],
                    "codes": [
                      "NEWSUB50"
                    ]
                  }
                },
                "createMultiTierSubscriptionDiscount": {
                  "summary": "Multi-tier subscription discount",
                  "value": {
                    "coupon": "annual-upgrade-promo",
                    "orderLevelDiscount": false,
                    "hasMultiDiscount": true,
                    "discounts": [
                      {
                        "type": "percent",
                        "percent": 50,
                        "products": [
                          "pro-plan-annual"
                        ],
                        "discountPeriodCount": 3,
                        "applyDiscountImmediately": true
                      },
                      {
                        "type": "percent",
                        "percent": 25,
                        "products": [
                          "pro-plan-monthly"
                        ],
                        "discountPeriodCount": 6,
                        "applyDiscountImmediately": false
                      }
                    ],
                    "combine": false,
                    "reason": {
                      "en": "Upgrade and save"
                    },
                    "codes": [
                      "ANNUAL50",
                      "ANNUAL25"
                    ]
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Returned for both successful operations and validation errors.\nAlways check the `result` field to determine the outcome.\n",
            "content": {
              "application/json": {
                "schema": {
                  "oneOf": [
                    {
                      "$ref": "#/components/schemas/UpsertSuccessResponse"
                    },
                    {
                      "$ref": "#/components/schemas/ErrorResponse"
                    }
                  ]
                },
                "examples": {
                  "created": {
                    "summary": "Coupon created",
                    "value": {
                      "coupon": "summer-sale-2026",
                      "action": "coupon.create",
                      "result": "success"
                    }
                  },
                  "updated": {
                    "summary": "Coupon updated",
                    "value": {
                      "coupon": "summer-sale-2026",
                      "action": "coupon.update",
                      "result": "success"
                    }
                  },
                  "invalidDiscountType": {
                    "summary": "Validation error — invalid discount type",
                    "value": {
                      "coupon": "summer-sale-2026",
                      "action": "coupon.create",
                      "result": "error",
                      "error": "Invalid discount type amount"
                    }
                  },
                  "orderLevelPercentConflict": {
                    "summary": "Validation error — order-level constraint conflict (Beta)",
                    "value": {
                      "coupon": "summer-sale-2026",
                      "action": "coupon.create",
                      "result": "error",
                      "error": "Order level discounts must use flat (amount) discount type, not percent."
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Returned when the request body contains unknown or unsupported fields. Returns `DeserializationErrorResponse`.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeserializationErrorResponse"
                },
                "examples": {
                  "unsupportedField": {
                    "summary": "Deserialization error — unsupported field sent",
                    "value": {
                      "message": "unreadable",
                      "params": []
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
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "schemas": {
      "CouponRequest": {
        "type": "object",
        "required": [
          "coupon"
        ],
        "properties": {
          "coupon": {
            "type": "string",
            "pattern": "^[a-zA-Z0-9_-]+$",
            "description": "Unique coupon path identifier. Accepts alphanumeric characters, hyphens, and underscores only.",
            "example": "summer-sale-2026"
          },
          "orderLevelDiscount": {
            "type": "boolean",
            "description": "**(Beta)** Applies the discount to the entire order subtotal\nrather than individual line items. The discount is proportionally\nallocated across eligible items, excluding add-ons and fees.\nTaxes are calculated after the discount is applied.\n\n**This field is part of the closed, invite-only Order-Level\nCoupons beta.** Only accounts enrolled in the beta can set this\nfield to `true`.\n\nWhen `true`, the following values are server-enforced regardless\nof what is sent in the request:\n\n| Field | Enforced value |\n|---|---|\n| `combine` | `true` |\n| `applyDiscountImmediately` | `true` |\n| `discountPeriodCount` | `1` |\n\nThe `discount.type` must be `flat` when `orderLevelDiscount`\nis `true`.\n",
            "example": true
          },
          "hasMultiDiscount": {
            "type": "boolean",
            "description": "Enables multi-tier discount mode for tiered subscription offers.\nWhen `true`, the `discounts` array is required in place of the\n`discount` object. Cannot be `true` if `orderLevelDiscount`\nis `true`.\n",
            "example": false
          },
          "discount": {
            "$ref": "#/components/schemas/DiscountConfig"
          },
          "discounts": {
            "type": "array",
            "minItems": 2,
            "maxItems": 25,
            "description": "Array of multi-tier discount configurations. Required when\n`hasMultiDiscount` is `true`. Tiers are automatically sorted\ndescending by duration.\n\nConstraints:\n- Must contain between 2 and 25 tiers.\n- A product path may only appear in one tier.\n- At most one tier may omit the `products` array (applies to\n  all products).\n",
            "items": {
              "$ref": "#/components/schemas/MultiDiscountTier"
            },
            "example": [
              {
                "type": "percent",
                "percent": 50,
                "products": [
                  "pro-plan-annual"
                ],
                "discountPeriodCount": 3,
                "applyDiscountImmediately": true
              },
              {
                "type": "percent",
                "percent": 25,
                "products": [
                  "pro-plan-monthly"
                ],
                "discountPeriodCount": 6,
                "applyDiscountImmediately": false
              }
            ]
          },
          "discountPeriodCount": {
            "type": "integer",
            "minimum": 0,
            "maximum": 365,
            "description": "Number of billing periods the discount applies to. Accepts\nvalues from `0` to `365`. `0` is treated as unlimited and\nstored as `null` in the response.\n\nIgnored and forced to `1` when `orderLevelDiscount` is `true`.\n",
            "example": 3
          },
          "applyDiscountImmediately": {
            "type": "boolean",
            "description": "Applies the discount starting in the first billing period rather\nthan the next renewal period. Requires a feature flag on your\naccount.\n\nIgnored and forced to `true` when `orderLevelDiscount` is `true`.\n",
            "example": true
          },
          "combine": {
            "type": "boolean",
            "description": "Allows this discount to stack with other active discounts. Forced to `true` when `orderLevelDiscount` is `true`.",
            "example": false
          },
          "autoSelectDiscount": {
            "type": "boolean",
            "description": "Automatically selects the most favorable discount for the\ncustomer when multiple discounts are applicable. Only applies\nwhen `combine` is `false`. Requires a feature flag on your\naccount.\n\n**Note:** This field is accepted without error but has no effect\nuntil the feature flag is enabled. It is not returned in GET\nresponses.\n",
            "example": true
          },
          "reason": {
            "type": "object",
            "description": "Localized description of the discount, visible to customers at\ncheckout. Provide each translation as a key-value pair using the\nISO 639-1 language code as the key (e.g. `en`, `de`, `fr`).\n",
            "properties": {
              "en": {
                "type": "string",
                "description": "English localization.",
                "example": "50% off for your first 3 months"
              }
            },
            "additionalProperties": {
              "type": "string"
            },
            "example": {
              "en": "50% off for your first 3 months",
              "de": "50% Rabatt für die ersten 3 Monate"
            }
          },
          "limit": {
            "type": "integer",
            "minimum": 0,
            "description": "Maximum number of times this discount can be applied across all\ncustomers and codes. `0` means unlimited.\n\n**Note:** This value is returned as a string in GET responses.\n`0` sent → `\"\"` received. Both represent unlimited.\n",
            "example": 500
          },
          "available": {
            "$ref": "#/components/schemas/Availability"
          },
          "codes": {
            "type": "array",
            "description": "Coupon codes that activate this discount at checkout.\n\n**Code handling**\n\n- **Case:** Codes are normalized to uppercase on storage.\n  `summer10` and `SUMMER10` are stored as the same code.\n- **Duplicates:** Duplicate codes within this array are\n  accepted without error and echoed verbatim in the response,\n  but storage silently dedupes (case-insensitively) to a\n  single entry per unique code. Call\n  `GET /coupons/{coupon_id}/codes` after the write to\n  confirm the stored state.\n- **Format:** Alphanumeric characters, hyphens, and\n  underscores only. Spaces and other special characters are\n  rejected.\n",
            "items": {
              "type": "string",
              "pattern": "^[a-zA-Z0-9_-]+$"
            },
            "example": [
              "SUMMER10",
              "SUMMER20"
            ]
          },
          "products": {
            "type": "array",
            "description": "Product path identifiers this discount applies to. An empty array applies the discount to all eligible products.",
            "items": {
              "type": "string"
            },
            "example": [
              "pro-plan-monthly",
              "pro-plan-annual"
            ]
          }
        }
      },
      "DiscountConfig": {
        "type": "object",
        "description": "Discount type and amount configuration.",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "percent",
              "flat"
            ],
            "description": "Discount type. **(Beta)** Must be `flat` when `orderLevelDiscount` is `true`.",
            "example": "percent"
          },
          "percent": {
            "type": "number",
            "description": "Percentage amount to discount. Required when `type` is `percent`. Decimal values are accepted.",
            "example": 25
          },
          "amount": {
            "type": "object",
            "description": "Per-currency flat discount amounts. Required when `type` is `flat`. All values must be greater than `0`.",
            "properties": {
              "USD": {
                "type": "number",
                "format": "float",
                "description": "US Dollar amount.",
                "example": 10
              },
              "EUR": {
                "type": "number",
                "format": "float",
                "description": "Euro amount.",
                "example": 9
              },
              "GBP": {
                "type": "number",
                "format": "float",
                "description": "British Pound amount.",
                "example": 8
              }
            },
            "additionalProperties": {
              "type": "number",
              "format": "float"
            }
          }
        }
      },
      "MultiDiscountTier": {
        "type": "object",
        "description": "A single discount tier within a multi-tier configuration.",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "percent",
              "flat"
            ],
            "description": "Discount type for this tier.",
            "example": "percent"
          },
          "percent": {
            "type": "number",
            "description": "Percentage amount for this tier. Required when `type` is `percent`. Decimal values are accepted.",
            "example": 50
          },
          "amount": {
            "type": "object",
            "description": "Per-currency flat discount amounts for this tier. Required when `type` is `flat`.",
            "properties": {
              "USD": {
                "type": "number",
                "format": "float",
                "example": 10
              },
              "EUR": {
                "type": "number",
                "format": "float",
                "example": 9
              },
              "GBP": {
                "type": "number",
                "format": "float",
                "example": 8
              }
            },
            "additionalProperties": {
              "type": "number",
              "format": "float"
            }
          },
          "products": {
            "type": "array",
            "description": "Product path identifiers this tier applies to. Omit to apply this tier to all products. At most one tier per coupon may omit this field.",
            "items": {
              "type": "string"
            },
            "example": [
              "pro-plan-annual"
            ]
          },
          "discountPeriodCount": {
            "type": "integer",
            "description": "Number of billing periods this tier's discount applies to.",
            "example": 3
          },
          "applyDiscountImmediately": {
            "type": "boolean",
            "description": "Applies this tier's discount starting in the first billing period rather than the next renewal period.",
            "example": true
          }
        }
      },
      "Availability": {
        "type": "object",
        "description": "Active date range for the coupon. When omitted, the coupon has no\ndate restrictions. Returns `{}` in GET responses when not configured.\n\nAccepted date formats for both `start` and `end`:\n- ISO 8601: `2026-06-01T00:00:00Z`\n- Date only: `2026-06-01`\n- Date and time: `2026-06-01 00:00`\n\nValidation rules:\n- `start` must not be a past date.\n- `end` must not be a past date.\n- `end` must be after `start`.\n",
        "properties": {
          "start": {
            "type": "string",
            "description": "Start date and time for coupon validity.",
            "example": "2026-06-01"
          },
          "end": {
            "type": "string",
            "description": "End date and time for coupon validity. Must be in the future and after `start`.",
            "example": "2026-08-31"
          }
        }
      },
      "UpsertSuccessResponse": {
        "type": "object",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon that was created or updated.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "enum": [
              "coupon.create",
              "coupon.update"
            ],
            "description": "Indicates whether the coupon was created or updated. Use this field to determine which operation was performed.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "success"
            ],
            "description": "Indicates the operation succeeded.",
            "example": "success"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      },
      "DeserializationErrorResponse": {
        "type": "object",
        "description": "Returned as a true `400 Bad Request` when the request body contains\nunknown or unsupported fields.\n",
        "properties": {
          "message": {
            "type": "string",
            "description": "Error category.",
            "example": "unreadable"
          },
          "params": {
            "type": "array",
            "description": "Additional error context. Currently always returns an empty array.",
            "items": {},
            "example": []
          }
        }
      }
    }
  }
}
```

Retrieve a coupon

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a coupon

Returns full details for a specific coupon by its path identifier.

# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons/{coupon_id}": {
      "get": {
        "tags": [
          "Coupons"
        ],
        "summary": "Retrieve a coupon",
        "description": "Returns full details for a specific coupon by its path identifier.",
        "operationId": "getCoupon",
        "parameters": [
          {
            "$ref": "#/components/parameters/coupon_id"
          },
          {
            "name": "expand",
            "in": "query",
            "required": false,
            "description": "When `true`, expands product path identifiers to full product objects in the `products` array of the response.",
            "schema": {
              "type": "boolean"
            },
            "example": true
          }
        ],
        "responses": {
          "200": {
            "description": "Successful response.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CouponResponse"
                },
                "examples": {
                  "retrieveItemLevelPercentDiscount": {
                    "summary": "Item-level percent discount",
                    "value": {
                      "coupon": "new-subscriber-offer",
                      "discount": {
                        "hasMultipleDiscounts": false,
                        "discounts": [],
                        "type": "percent",
                        "percent": 50
                      },
                      "discountPeriodCount": 3,
                      "applyDiscountImmediately": true,
                      "combine": false,
                      "reason": {
                        "en": "50% off for your first 3 months"
                      },
                      "limit": "500",
                      "available": {
                        "start": "2026-06-01",
                        "end": "2026-08-31"
                      },
                      "codes": [
                        "NEWSUB50"
                      ],
                      "products": [
                        "pro-plan-monthly",
                        "pro-plan-annual"
                      ]
                    }
                  },
                  "retrieveOrderLevelFlatDiscount": {
                    "summary": "Order-level flat discount (Beta)",
                    "value": {
                      "coupon": "summer-sale-2026",
                      "discount": {
                        "hasMultipleDiscounts": false,
                        "discounts": [],
                        "type": "flat",
                        "amount": {
                          "USD": 10,
                          "EUR": 9,
                          "GBP": 8
                        }
                      },
                      "discountPeriodCount": 1,
                      "applyDiscountImmediately": true,
                      "combine": true,
                      "orderLevelDiscount": true,
                      "reason": {
                        "en": "Summer Sale — $10 off your order"
                      },
                      "limit": "500",
                      "available": {
                        "start": "2026-06-01",
                        "end": "2026-08-31"
                      },
                      "codes": [
                        "SUMMER10"
                      ],
                      "products": []
                    }
                  },
                  "retrieveMultiTierSubscriptionDiscount": {
                    "summary": "Multi-tier subscription discount",
                    "value": {
                      "coupon": "annual-upgrade-promo",
                      "discount": {
                        "hasMultipleDiscounts": true,
                        "discounts": [
                          {
                            "type": "percent",
                            "percent": 50,
                            "products": [
                              "pro-plan-annual"
                            ],
                            "discountPeriodCount": 3,
                            "applyDiscountImmediately": true
                          },
                          {
                            "type": "percent",
                            "percent": 25,
                            "products": [
                              "pro-plan-monthly"
                            ],
                            "discountPeriodCount": 6,
                            "applyDiscountImmediately": false
                          }
                        ]
                      },
                      "combine": false,
                      "reason": {
                        "en": "Upgrade and save"
                      },
                      "limit": "",
                      "available": {},
                      "codes": [
                        "ANNUAL50",
                        "ANNUAL25"
                      ],
                      "products": [
                        "pro-plan-annual",
                        "pro-plan-monthly"
                      ]
                    }
                  }
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/ValidationError"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "parameters": {
      "coupon_id": {
        "name": "coupon_id",
        "in": "path",
        "required": true,
        "description": "The unique path identifier for the coupon.",
        "schema": {
          "type": "string"
        },
        "example": "summer-sale-2026"
      }
    },
    "responses": {
      "ValidationError": {
        "description": "Validation or request error. Returns `200 OK` — always check the `result` field rather than relying on the HTTP status code alone.",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/ErrorResponse"
            }
          }
        }
      }
    },
    "schemas": {
      "DiscountConfig": {
        "type": "object",
        "description": "Discount type and amount configuration.",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "percent",
              "flat"
            ],
            "description": "Discount type. **(Beta)** Must be `flat` when `orderLevelDiscount` is `true`.",
            "example": "percent"
          },
          "percent": {
            "type": "number",
            "description": "Percentage amount to discount. Required when `type` is `percent`. Decimal values are accepted.",
            "example": 25
          },
          "amount": {
            "type": "object",
            "description": "Per-currency flat discount amounts. Required when `type` is `flat`. All values must be greater than `0`.",
            "properties": {
              "USD": {
                "type": "number",
                "format": "float",
                "description": "US Dollar amount.",
                "example": 10
              },
              "EUR": {
                "type": "number",
                "format": "float",
                "description": "Euro amount.",
                "example": 9
              },
              "GBP": {
                "type": "number",
                "format": "float",
                "description": "British Pound amount.",
                "example": 8
              }
            },
            "additionalProperties": {
              "type": "number",
              "format": "float"
            }
          }
        }
      },
      "MultiDiscountTier": {
        "type": "object",
        "description": "A single discount tier within a multi-tier configuration.",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "percent",
              "flat"
            ],
            "description": "Discount type for this tier.",
            "example": "percent"
          },
          "percent": {
            "type": "number",
            "description": "Percentage amount for this tier. Required when `type` is `percent`. Decimal values are accepted.",
            "example": 50
          },
          "amount": {
            "type": "object",
            "description": "Per-currency flat discount amounts for this tier. Required when `type` is `flat`.",
            "properties": {
              "USD": {
                "type": "number",
                "format": "float",
                "example": 10
              },
              "EUR": {
                "type": "number",
                "format": "float",
                "example": 9
              },
              "GBP": {
                "type": "number",
                "format": "float",
                "example": 8
              }
            },
            "additionalProperties": {
              "type": "number",
              "format": "float"
            }
          },
          "products": {
            "type": "array",
            "description": "Product path identifiers this tier applies to. Omit to apply this tier to all products. At most one tier per coupon may omit this field.",
            "items": {
              "type": "string"
            },
            "example": [
              "pro-plan-annual"
            ]
          },
          "discountPeriodCount": {
            "type": "integer",
            "description": "Number of billing periods this tier's discount applies to.",
            "example": 3
          },
          "applyDiscountImmediately": {
            "type": "boolean",
            "description": "Applies this tier's discount starting in the first billing period rather than the next renewal period.",
            "example": true
          }
        }
      },
      "CouponResponse": {
        "type": "object",
        "description": "Full coupon details.",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Unique coupon path identifier.",
            "example": "summer-sale-2026"
          },
          "discount": {
            "$ref": "#/components/schemas/DiscountResponseConfig"
          },
          "discountPeriodCount": {
            "type": "integer",
            "nullable": true,
            "description": "Number of billing periods the discount applies to. `null` means unlimited.",
            "example": 3
          },
          "applyDiscountImmediately": {
            "type": "boolean",
            "description": "Indicates the discount applies starting in the first billing\nperiod.\n\n**Note:** Only present in the response when `true`. Absence\nof this field indicates `false`.\n",
            "example": true
          },
          "combine": {
            "type": "boolean",
            "description": "Indicates whether this discount stacks with other active discounts.",
            "example": false
          },
          "orderLevelDiscount": {
            "type": "boolean",
            "description": "**(Beta)** Indicates the discount applies to the entire order\nsubtotal. Only returned for coupons created by accounts enrolled\nin the closed Order-Level Coupons beta.\n\n**Note:** Only present in the response when `true`. Absence\nof this field indicates `false`.\n",
            "example": true
          },
          "reason": {
            "type": "object",
            "description": "Localized discount description. Returns `{}` when no reason is configured.",
            "additionalProperties": {
              "type": "string"
            },
            "example": {
              "en": "50% off for your first 3 months"
            }
          },
          "limit": {
            "type": "string",
            "description": "Maximum number of times this discount can be applied. Returns\nas a string. `\"\"` means unlimited.\n\n**Note:** Sending `limit: 0` on create or update is stored\nand returned as `\"\"`.\n",
            "example": "500"
          },
          "available": {
            "type": "object",
            "description": "Active date range for the coupon. Returns `{}` when no date restrictions are configured.",
            "properties": {
              "start": {
                "type": "string",
                "example": "2026-06-01"
              },
              "end": {
                "type": "string",
                "example": "2026-08-31"
              }
            }
          },
          "codes": {
            "type": "array",
            "description": "Coupon codes associated with this coupon.\n\n**Code handling**\n\n- **Case:** Codes are normalized to uppercase on storage. A\n  code submitted as `summer10` is stored and returned as\n  `SUMMER10`. Case is not preserved — treat the uppercased\n  form as canonical.\n- **Pagination:** Limited to 1,000 items on this endpoint.\n  Use `GET /coupons/{coupon_id}/codes` to retrieve the\n  full list for coupons with more than 1,000 codes.\n",
            "items": {
              "type": "string"
            },
            "example": [
              "SUMMER10",
              "SUMMER20"
            ]
          },
          "products": {
            "type": "array",
            "description": "Product path identifiers this discount applies to. Returns an empty array if the discount applies to all products. Returns full product objects when the `expand` query parameter is `true`.",
            "items": {},
            "example": [
              "pro-plan-monthly",
              "pro-plan-annual"
            ]
          }
        }
      },
      "DiscountResponseConfig": {
        "allOf": [
          {
            "$ref": "#/components/schemas/DiscountConfig"
          },
          {
            "type": "object",
            "properties": {
              "hasMultipleDiscounts": {
                "type": "boolean",
                "description": "Indicates whether this coupon uses a multi-tier discount configuration.",
                "example": false
              },
              "discounts": {
                "type": "array",
                "description": "Array of multi-tier discount configurations. Populated only when `hasMultipleDiscounts` is `true`.",
                "items": {
                  "$ref": "#/components/schemas/MultiDiscountTier"
                },
                "example": []
              }
            }
          }
        ]
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      }
    }
  }
}
```
Add coupon codes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Add coupon codes

Adds new codes to an existing coupon. This operation is additive —
existing codes are preserved and the new codes are appended.

**Code handling**

- **Case:** Codes are normalized to uppercase on storage. `summer10`
  and `SUMMER10` are treated as the same code.
- **Duplicates:** Duplicate codes within a single request are
  accepted without error and echoed verbatim in the response, but
  storage silently dedupes to a single entry per unique
  (case-insensitive) code. Call
  `GET /coupons/{coupon_id}/codes` after a write to confirm the
  stored state.
- **Format:** Codes must contain only alphanumeric characters,
  hyphens, and underscores. Spaces and other special characters
  are rejected.


# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons/{coupon_id}": {
      "post": {
        "tags": [
          "Coupons"
        ],
        "summary": "Add coupon codes",
        "description": "Adds new codes to an existing coupon. This operation is additive —\nexisting codes are preserved and the new codes are appended.\n\n**Code handling**\n\n- **Case:** Codes are normalized to uppercase on storage. `summer10`\n  and `SUMMER10` are treated as the same code.\n- **Duplicates:** Duplicate codes within a single request are\n  accepted without error and echoed verbatim in the response, but\n  storage silently dedupes to a single entry per unique\n  (case-insensitive) code. Call\n  `GET /coupons/{coupon_id}/codes` after a write to confirm the\n  stored state.\n- **Format:** Codes must contain only alphanumeric characters,\n  hyphens, and underscores. Spaces and other special characters\n  are rejected.\n",
        "operationId": "addCouponCodes",
        "parameters": [
          {
            "$ref": "#/components/parameters/coupon_id"
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/AddCodesRequest"
              },
              "example": {
                "codes": [
                  "SUMMER10",
                  "SUMMER20",
                  "SUMMER30"
                ]
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Codes successfully added.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/AddCodesSuccessResponse"
                },
                "example": {
                  "coupon": "summer-sale-2026",
                  "codes": [
                    "SUMMER10",
                    "SUMMER20",
                    "SUMMER30"
                  ],
                  "result": "success"
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/ValidationError"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "parameters": {
      "coupon_id": {
        "name": "coupon_id",
        "in": "path",
        "required": true,
        "description": "The unique path identifier for the coupon.",
        "schema": {
          "type": "string"
        },
        "example": "summer-sale-2026"
      }
    },
    "responses": {
      "ValidationError": {
        "description": "Validation or request error. Returns `200 OK` — always check the `result` field rather than relying on the HTTP status code alone.",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/ErrorResponse"
            }
          }
        }
      }
    },
    "schemas": {
      "AddCodesRequest": {
        "type": "object",
        "required": [
          "codes"
        ],
        "properties": {
          "codes": {
            "type": "array",
            "description": "Coupon codes to add to the coupon.\n\n**Code handling**\n\n- **Case:** Codes are normalized to uppercase on storage.\n  `summer10` and `SUMMER10` are stored as the same code.\n- **Duplicates:** Duplicate codes within a single request do\n  not cause the request to be rejected. The API accepts the\n  request, returns `result: success`, and echoes every\n  submitted code verbatim in the response. However, storage\n  silently dedupes (case-insensitively) to a single entry per\n  unique code. Call `GET /coupons/{coupon_id}/codes`\n  after the write to confirm the stored state.\n- **Format:** Alphanumeric characters, hyphens, and\n  underscores only. Spaces and other special characters are\n  rejected.\n",
            "items": {
              "type": "string",
              "pattern": "^[a-zA-Z0-9_-]+$"
            },
            "example": [
              "SUMMER10",
              "SUMMER20",
              "SUMMER30"
            ]
          }
        }
      },
      "AddCodesSuccessResponse": {
        "type": "object",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon the codes were added to.",
            "example": "summer-sale-2026"
          },
          "codes": {
            "type": "array",
            "description": "The codes as they were submitted in the request — echoed\nverbatim, including any case variation.\n\n**Code handling**\n\n- **Request echo, not stored state:** This array reflects the\n  request payload, not what was persisted. Codes are\n  normalized to uppercase on storage, and duplicates\n  (including case-insensitive duplicates) are silently\n  deduped. The response is not a reliable indicator of what\n  was actually stored.\n- **Verification:** Call `GET /coupons/{coupon_id}/codes`\n  after a write to confirm the stored codes.\n",
            "items": {
              "type": "string"
            },
            "example": [
              "SUMMER10",
              "SUMMER20",
              "SUMMER30"
            ]
          },
          "result": {
            "type": "string",
            "enum": [
              "success"
            ],
            "description": "Indicates the operation succeeded.",
            "example": "success"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      }
    }
  }
}
```
List all coupon codes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all coupon codes

Returns all codes associated with a coupon.

**Note:** Use this endpoint to retrieve the full list of codes for
coupons with more than 1,000 codes. The `GET /coupons/{coupon_id}`
endpoint returns a maximum of 1,000 codes.


# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons/{coupon_id}/codes": {
      "get": {
        "tags": [
          "Coupons"
        ],
        "summary": "List all coupon codes",
        "description": "Returns all codes associated with a coupon.\n\n**Note:** Use this endpoint to retrieve the full list of codes for\ncoupons with more than 1,000 codes. The `GET /coupons/{coupon_id}`\nendpoint returns a maximum of 1,000 codes.\n",
        "operationId": "listCouponCodes",
        "parameters": [
          {
            "$ref": "#/components/parameters/coupon_id"
          }
        ],
        "responses": {
          "200": {
            "description": "Successful response.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CouponCodesResponse"
                },
                "example": {
                  "coupon": "summer-sale-2026",
                  "codes": [
                    "SUMMER10",
                    "SUMMER20",
                    "SUMMER30"
                  ]
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/ValidationError"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "parameters": {
      "coupon_id": {
        "name": "coupon_id",
        "in": "path",
        "required": true,
        "description": "The unique path identifier for the coupon.",
        "schema": {
          "type": "string"
        },
        "example": "summer-sale-2026"
      }
    },
    "responses": {
      "ValidationError": {
        "description": "Validation or request error. Returns `200 OK` — always check the `result` field rather than relying on the HTTP status code alone.",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/ErrorResponse"
            }
          }
        }
      }
    },
    "schemas": {
      "CouponCodesResponse": {
        "type": "object",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon.",
            "example": "summer-sale-2026"
          },
          "codes": {
            "type": "array",
            "description": "All coupon codes associated with the coupon.\n\n**Note:** Codes are normalized to uppercase on storage and are\nalways returned in uppercase, regardless of the case used when\nthey were submitted.\n",
            "items": {
              "type": "string"
            },
            "example": [
              "SUMMER10",
              "SUMMER20",
              "SUMMER30"
            ]
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      }
    }
  }
}
```

Delete all coupon codes

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Delete all coupon codes

Permanently deletes all codes for a coupon.

**Warning:** After this operation, the coupon will have no active
codes and cannot be applied at checkout until new codes are added
via `POST /coupons/{coupon_id}`.


# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "FastSpring API — Coupons",
    "description": "Create, retrieve, update, and delete coupons and coupon codes.\n",
    "version": "1.0.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://api.fastspring.com"
    }
  ],
  "security": [
    {
      "basicAuth": []
    }
  ],
  "tags": [
    {
      "name": "Coupons",
      "description": "Manage promotional coupons and coupon codes."
    }
  ],
  "paths": {
    "/coupons/{coupon_id}/codes": {
      "delete": {
        "tags": [
          "Coupons"
        ],
        "summary": "Delete all coupon codes",
        "description": "Permanently deletes all codes for a coupon.\n\n**Warning:** After this operation, the coupon will have no active\ncodes and cannot be applied at checkout until new codes are added\nvia `POST /coupons/{coupon_id}`.\n",
        "operationId": "deleteCouponCodes",
        "parameters": [
          {
            "$ref": "#/components/parameters/coupon_id"
          }
        ],
        "responses": {
          "200": {
            "description": "Codes successfully deleted.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteCodesSuccessResponse"
                },
                "example": {
                  "coupon": "summer-sale-2026",
                  "codes deleted": [
                    "SUMMER10",
                    "SUMMER20",
                    "SUMMER30"
                  ],
                  "action": "delete coupon codes",
                  "result": "success"
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/ValidationError"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "basicAuth": {
        "type": "http",
        "scheme": "basic"
      }
    },
    "parameters": {
      "coupon_id": {
        "name": "coupon_id",
        "in": "path",
        "required": true,
        "description": "The unique path identifier for the coupon.",
        "schema": {
          "type": "string"
        },
        "example": "summer-sale-2026"
      }
    },
    "responses": {
      "ValidationError": {
        "description": "Validation or request error. Returns `200 OK` — always check the `result` field rather than relying on the HTTP status code alone.",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/ErrorResponse"
            }
          }
        }
      }
    },
    "schemas": {
      "DeleteCodesSuccessResponse": {
        "type": "object",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon whose codes were deleted.",
            "example": "summer-sale-2026"
          },
          "codes deleted": {
            "type": "array",
            "description": "The codes that were deleted.",
            "items": {
              "type": "string"
            },
            "example": [
              "SUMMER10",
              "SUMMER20",
              "SUMMER30"
            ]
          },
          "action": {
            "type": "string",
            "description": "The operation performed.",
            "example": "delete coupon codes"
          },
          "result": {
            "type": "string",
            "enum": [
              "success"
            ],
            "description": "Indicates the operation succeeded.",
            "example": "success"
          }
        }
      },
      "ErrorResponse": {
        "type": "object",
        "description": "Returned for validation failures and request errors.\n\n**Note:** These errors return `200 OK`. Always check the `result`\nfield rather than relying on the HTTP status code alone.\n",
        "properties": {
          "coupon": {
            "type": "string",
            "description": "Path identifier of the coupon relevant to the error.",
            "example": "summer-sale-2026"
          },
          "action": {
            "type": "string",
            "description": "The operation that was attempted.",
            "example": "coupon.create"
          },
          "result": {
            "type": "string",
            "enum": [
              "error"
            ],
            "description": "Indicates the operation failed.",
            "example": "error"
          },
          "error": {
            "type": "string",
            "description": "Description of why the request failed.",
            "example": "Invalid coupon code SUMMER@10"
          }
        }
      }
    }
  }
}
```