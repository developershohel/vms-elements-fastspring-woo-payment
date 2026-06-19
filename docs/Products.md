Create or update products

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create or update products

Creates products or updates existing products.

If you are creating products in bulk, you can add up to 300 at a time.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products": {
      "post": {
        "summary": "Create or update products",
        "tags": [
          "Products"
        ],
        "description": "Creates products or updates existing products.\n\nIf you are creating products in bulk, you can add up to 300 at a time.\n",
        "operationId": "Createoneormorenewproducts",
        "deprecated": false,
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateoneormorenewproductsRequest"
              },
              "examples": {
                "createProduct": {
                  "summary": "Create a new product",
                  "description": "Request body example for creating a new product with basic details.",
                  "value": {
                    "products": [
                      {
                        "product": "new-laptop",
                        "display": {
                          "en": "New Laptop"
                        },
                        "pricing": {
                          "price": {
                            "USD": 444.99,
                            "EUR": 899.99
                          },
                          "paymentCollected": true,
                          "paidTrial": false,
                          "quantityDiscounts": {
                            "10": 25
                          },
                          "discountReason": {
                            "en": "Seasonal Promotion"
                          },
                          "trial": 14,
                          "interval": "month",
                          "intervalLength": 1,
                          "quantityBehavior": "allow",
                          "discountDuration": 1,
                          "reminderNotification": {
                            "enabled": true,
                            "interval": "day",
                            "intervalLength": 3
                          },
                          "overdueNotification": {
                            "enabled": true,
                            "interval": "day",
                            "intervalLength": 3,
                            "amount": 10
                          },
                          "cancellation": {
                            "interval": "month",
                            "intervalLength": 2
                          }
                        },
                        "description": {
                          "summary": {
                            "en": "A lightweight, high-performance laptop."
                          },
                          "action": {
                            "en": "Buy now to experience unparalleled portability."
                          },
                          "full": {
                            "en": "The New Laptop is designed for students and professionals on the go."
                          }
                        },
                        "fulfillment": {
                          "instructions": {
                            "en": "Ships within 2-3 business days.",
                            "es": "Se envía en 2-3 días hábiles."
                          }
                        },
                        "attributes": {
                          "color": "Silver",
                          "storage": "512GB SSD"
                        },
                        "image": "https://example.com/images/new-laptop.jpg",
                        "format": "digital",
                        "sku": "sku-98765",
                        "badge": {
                          "en": "Best Value"
                        },
                        "rank": 1
                      }
                    ]
                  }
                },
                "updateProduct": {
                  "summary": "Update an existing product",
                  "description": "Request body example for updating product details such as price and display information.",
                  "value": {
                    "products": [
                      {
                        "product": "existing-laptop",
                        "display": {
                          "en": "Updated Laptop Name"
                        },
                        "pricing": {
                          "price": {
                            "USD": 899.99,
                            "EUR": 799.99
                          }
                        }
                      }
                    ]
                  }
                },
                "createTrialWithoutPayment": {
                  "summary": "Create a trial subscription without requiring a payment method",
                  "description": "Request body example demonstrating how to create a trial subscription with a trial period and optional payment requirements.\n- `paymentCollected`: If `true`, payment is collected upfront. Defaults to `true` if not specified.\n- `paidTrial`: If `true`, a payment method is required for the trial. If `false`, the trial is free.\n- `trialPrice`: Required if `paidTrial` is `true`. Ignored if `paidTrial` is `false`.\n",
                  "value": {
                    "products": [
                      {
                        "product": "trial-plan-premium",
                        "display": {
                          "en": "Premium Plan Trial - No Payment Required"
                        },
                        "description": {
                          "summary": {
                            "en": "Start your free trial of the Premium Plan today. No payment method needed."
                          }
                        },
                        "pricing": {
                          "quantityDefault": 1,
                          "interval": "month",
                          "intervalLength": 1,
                          "trial": 30,
                          "paymentCollected": false,
                          "paidTrial": false,
                          "trialPrice": {
                            "USD": 0,
                            "EUR": 0
                          },
                          "price": {
                            "USD": 19.99,
                            "EUR": 17.99
                          }
                        }
                      }
                    ]
                  }
                },
                "setBadgeAndRank": {
                  "summary": "Set badge and rank for products",
                  "description": "Request body example for setting badge and rank attributes to highlight certain products.",
                  "value": {
                    "products": [
                      {
                        "product": "basic",
                        "badge": {
                          "en": "Basic"
                        },
                        "rank": 2
                      },
                      {
                        "product": "premium",
                        "badge": {
                          "en": "Best Value"
                        },
                        "rank": 1
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
                  "oneOf": [
                    {
                      "$ref": "#/components/schemas/CreateProductsResponse"
                    },
                    {
                      "$ref": "#/components/schemas/UpdateProductsResponse"
                    }
                  ]
                },
                "examples": {
                  "CreateProductsResponseExample": {
                    "summary": "Create Products Response",
                    "value": {
                      "products": [
                        {
                          "product": "new-laptop",
                          "action": "product.create",
                          "created.id": "kyCJS3ovQ4qhfjA-PPzv_g",
                          "result": "success"
                        }
                      ]
                    }
                  },
                  "UpdateProductsResponseExample": {
                    "summary": "Update Products Response",
                    "value": {
                      "products": [
                        {
                          "product": "existing-laptop",
                          "action": "product.update",
                          "updated.id": "kyCJS3ovQ4qhfjA-PPzv_g",
                          "result": "success"
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
      "CreateoneormorenewproductsRequest": {
        "type": "object",
        "required": [
          "products"
        ],
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "required": [
                "product",
                "display",
                "pricing"
              ],
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product path ID.",
                  "example": "premium-laptop"
                },
                "display": {
                  "type": "object",
                  "description": "Display information for the product in different languages.",
                  "properties": {
                    "en": {
                      "type": "string",
                      "description": "The product's name in English.",
                      "example": "Premium Laptop"
                    }
                  }
                },
                "pricing": {
                  "type": "object",
                  "description": "Pricing details for the product.",
                  "required": [
                    "price"
                  ],
                  "properties": {
                    "price": {
                      "type": "object",
                      "description": "The price for the product in different currencies.",
                      "required": [
                        "USD"
                      ],
                      "properties": {
                        "USD": {
                          "type": "number",
                          "description": "Price in USD.",
                          "example": 1999.99
                        },
                        "EUR": {
                          "type": "number",
                          "description": "Price in EUR.",
                          "example": 1799.99
                        }
                      }
                    },
                    "trial": {
                      "type": "integer",
                      "description": "The trial period for the product, in days. Only needed if you are creating a subscription.",
                      "example": 14
                    },
                    "interval": {
                      "type": "string",
                      "description": "The billing interval for the product.",
                      "enum": [
                        "week",
                        "month",
                        "year"
                      ],
                      "example": "month"
                    },
                    "intervalLength": {
                      "type": "integer",
                      "description": "Length of the billing interval.",
                      "example": 1
                    },
                    "quantityBehavior": {
                      "type": "string",
                      "description": "Defines the quantity behavior for the product.",
                      "enum": [
                        "allow",
                        "lock",
                        "hide"
                      ],
                      "example": "allow"
                    },
                    "quantityDefault": {
                      "type": "integer",
                      "description": "The default quantity for the product.",
                      "example": 1
                    },
                    "paymentCollected": {
                      "type": "boolean",
                      "description": "Whether payment is collected upfront.",
                      "example": true,
                      "default": true
                    },
                    "paidTrial": {
                      "type": "boolean",
                      "description": "Whether the trial is paid.",
                      "example": false,
                      "default": false
                    },
                    "trialPrice": {
                      "type": "object",
                      "description": "Trial pricing in different currencies. Required if `paidTrial` is true.",
                      "properties": {
                        "USD": {
                          "type": "number",
                          "description": "Trial price in USD.",
                          "example": 14.95
                        },
                        "EUR": {
                          "type": "number",
                          "description": "Trial price in EUR.",
                          "example": 12.99
                        }
                      }
                    },
                    "quantityDiscounts": {
                      "type": "object",
                      "additionalProperties": {
                        "type": "number",
                        "format": "double"
                      },
                      "description": "Defines the discount applied when a minimum order quantity is reached.\n",
                      "example": {
                        "30": {
                          "USD": 25,
                          "EUR": 15
                        }
                      }
                    },
                    "discountReason": {
                      "type": "object",
                      "description": "Reason for the discount in various languages.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "description": "Discount reason in English.",
                          "example": "Seasonal Promotion"
                        }
                      }
                    },
                    "discountDuration": {
                      "type": "integer",
                      "description": "Duration (in billing intervals) that the discount applies.",
                      "example": 1
                    },
                    "reminderNotification": {
                      "type": "object",
                      "description": "Configuration for reminder notifications sent before a charge.",
                      "properties": {
                        "enabled": {
                          "type": "boolean",
                          "description": "Whether reminder notifications are enabled.",
                          "example": true
                        },
                        "interval": {
                          "type": "string",
                          "description": "Unit of time used for the reminder interval.",
                          "example": "day",
                          "enum": [
                            "day",
                            "week",
                            "month",
                            "year"
                          ]
                        },
                        "intervalLength": {
                          "type": "integer",
                          "description": "The number of interval units before the charge when the reminder is sent.",
                          "example": 3
                        }
                      }
                    },
                    "overdueNotification": {
                      "type": "object",
                      "description": "Configuration for overdue notifications sent after a failed payment attempt.",
                      "properties": {
                        "enabled": {
                          "type": "boolean",
                          "description": "Whether overdue notifications are enabled.",
                          "example": true
                        },
                        "interval": {
                          "type": "string",
                          "description": "Unit of time used for the overdue notification interval.",
                          "example": "day",
                          "enum": [
                            "day",
                            "week",
                            "month",
                            "year"
                          ]
                        },
                        "intervalLength": {
                          "type": "integer",
                          "description": "Number of interval units before the overdue notification is sent.",
                          "example": 3
                        },
                        "amount": {
                          "type": "number",
                          "description": "Amount included in the overdue notification (for example, a past-due amount).",
                          "example": 10
                        }
                      }
                    },
                    "cancellation": {
                      "type": "object",
                      "description": "Defines the cancellation grace period before the subscription is cancelled.\n\n**Note:** Changes to the `cancellation` object update the same **Deactivation Settings** shown in the FastSpring app. If you set this value in the API, it overrides the UI configuration for the product.\n",
                      "properties": {
                        "interval": {
                          "type": "string",
                          "description": "Unit of time used for the cancellation window.",
                          "example": "month",
                          "enum": [
                            "day",
                            "week",
                            "month"
                          ]
                        },
                        "intervalLength": {
                          "type": "integer",
                          "description": "Number of interval units before cancellation takes effect.",
                          "example": 2
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
      "CreateProductsResponse": {
        "title": "Create Products Response",
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product identifier.",
                  "example": "premium-laptop"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the product.",
                  "example": "product.create"
                },
                "created.id": {
                  "type": "string",
                  "description": "The unique identifier for the created product.",
                  "example": "kyCJS3ovQ4qhfjA-PPzv_g"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "UpdateProductsResponse": {
        "title": "Update Products Response",
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "description": "List of products with their update status.",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product identifier.",
                  "example": "premium-laptop"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the product.",
                  "example": "product.update"
                },
                "updated.id": {
                  "type": "string",
                  "description": "The unique identifier for the updated product.",
                  "example": "kyCJS3ovQ4qhfjA-PPzv_g"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
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

List all product paths

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all product paths

Returns a list of all product path IDs.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products": {
      "get": {
        "summary": "List all product paths",
        "tags": [
          "Products"
        ],
        "description": "Returns a list of all product path IDs.",
        "operationId": "Getlistofallproductids",
        "deprecated": false,
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllProducts"
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
      "GetAllProducts": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API call.",
            "example": "products.getall"
          },
          "result": {
            "type": "string",
            "description": "The result of the action (e.g., success or error).",
            "example": "success"
          },
          "page": {
            "type": "integer",
            "description": "The page number of results returned.",
            "example": null
          },
          "limit": {
            "type": "integer",
            "description": "The limit of how many objects were returned per page.",
            "example": null
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results returned.",
            "example": null
          },
          "total": {
            "type": "integer",
            "description": "The total number of product paths returned.",
            "example": 0
          },
          "products": {
            "type": "array",
            "description": "List of product paths.",
            "items": {
              "type": "string"
            },
            "example": [
              "product-path-1",
              "product-path-2",
              "product-path-3"
            ]
          }
        }
      }
    }
  }
}
```

Retrieve a product

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a product

Retrieves the details of an existing product with the given `product_path`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/{product_path}": {
      "parameters": [
        {
          "name": "product_path",
          "in": "path",
          "required": true,
          "description": "Unique identifier to reference a specific product, also known as the product path ID.",
          "example": "premium-laptop",
          "schema": {
            "type": "string"
          }
        }
      ],
      "get": {
        "summary": "Retrieve a product",
        "tags": [
          "Products"
        ],
        "description": "Retrieves the details of an existing product with the given `product_path`.",
        "operationId": "Getproductsbyid",
        "deprecated": false,
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetProductsByPth"
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
      "GetProductsByPth": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product path ID.",
                  "example": "premium-laptop"
                },
                "parent": {
                  "type": "string",
                  "description": "The parent product, if applicable.",
                  "example": null
                },
                "productAppReference": {
                  "type": "string",
                  "description": "Unique reference ID for the product in the app.",
                  "example": "abcDEFgHiJklM1N-3OP9q"
                },
                "display": {
                  "type": "object",
                  "description": "Display information for the product in different languages.",
                  "properties": {
                    "en": {
                      "type": "string",
                      "description": "The product's name in English.",
                      "example": "FastSpring Falcon"
                    }
                  }
                },
                "description": {
                  "type": "object",
                  "description": "Description details for the product.",
                  "properties": {
                    "summary": {
                      "type": "object",
                      "description": "A brief summary of the product.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "description": "The product summary in English.",
                          "example": "The original FastSpring Falcon"
                        }
                      }
                    },
                    "action": {
                      "type": "object",
                      "description": "A call to action related to the product, prompting the customer to take an action (e.g., purchase, learn more).",
                      "properties": {
                        "en": {
                          "type": "string",
                          "description": "The action text in English.",
                          "example": "Order now and experience cutting-edge technology."
                        }
                      }
                    },
                    "full": {
                      "type": "object",
                      "description": "A full detailed description of the product, often used for more in-depth product information.",
                      "properties": {
                        "en": {
                          "type": "string",
                          "description": "The full description of the product in English.",
                          "example": "The Premium Laptop comes with an Intel Core i9 processor, 16GB of RAM, and a 1TB SSD. It's designed for power users who demand speed and efficiency in a sleek, portable package."
                        }
                      }
                    }
                  }
                },
                "image": {
                  "type": "string",
                  "description": "URL to the product image.",
                  "example": "https://image.com/furious-falcon-logo.png"
                },
                "sku": {
                  "type": "string",
                  "description": "Stock Keeping Unit, a unique identifier for the product in the inventory system.",
                  "example": "sku-12345"
                },
                "visibility": {
                  "type": "string",
                  "description": "Indicates whether a product is shown in the public catalog (public) or hidden from buyers (private).",
                  "enum": [
                    "private",
                    "public"
                  ],
                  "example": "public"
                },
                "quotable": {
                  "type": "boolean",
                  "description": "Indicates whether this product can be included in seller-generated quotes (true) or not (false).",
                  "example": true
                },
                "offers": {
                  "type": "array",
                  "description": "List of offers associated with the product.",
                  "items": {
                    "type": "object",
                    "properties": {
                      "type": {
                        "type": "string",
                        "description": "A specific type of offer available for the product.",
                        "enum": [
                          "upsell",
                          "downsell",
                          "cross-sell",
                          "upgrade",
                          "crossgrade",
                          "downgrade",
                          "addon",
                          "alternatives"
                        ],
                        "example": "addon"
                      },
                      "display": {
                        "type": "object",
                        "properties": {
                          "en": {
                            "type": "string",
                            "description": "The message or description for the offer in English.",
                            "example": "Discover our complementary products to maximize your results."
                          }
                        }
                      },
                      "items": {
                        "type": "array",
                        "description": "List of product items included in the offer.",
                        "items": {
                          "type": "string",
                          "example": "wireless-mouse"
                        }
                      },
                      "fulfillments": {
                        "type": "object",
                        "description": "Details of fulfillment for the offer.",
                        "properties": {
                          "sub-auto-addon_file_0": {
                            "type": "object",
                            "description": "Fulfillment data for a specific item.",
                            "properties": {
                              "fulfillment": {
                                "type": "string",
                                "description": "Fulfillment item name.",
                                "example": "sub-auto-addon_file_0"
                              },
                              "name": {
                                "type": "string",
                                "description": "The type of fulfillment.",
                                "example": "File Download (signed.pdf)"
                              },
                              "applicability": {
                                "type": "string",
                                "description": "Specifies if and when the fulfillment action will be applicable in a particular order.",
                                "enum": [
                                  "ALWAYS",
                                  "BASE",
                                  "CONFIGURATION",
                                  "REBILL_ONLY",
                                  "NON_REBILL_ONLY"
                                ],
                                "example": "NON_REBILL_ONLY"
                              },
                              "display": {
                                "type": "string",
                                "description": "Fulfillment file name. The extension must match file specified in URL.",
                                "example": "sub-auto-addon_file.pdf"
                              },
                              "url": {
                                "type": "string",
                                "description": "External URL for the product file to be retrieved.",
                                "example": null
                              },
                              "size": {
                                "type": "string",
                                "description": "The size of the product file.",
                                "example": null
                              },
                              "behavior": {
                                "type": "string",
                                "description": "Behavior setting for the fulfillment.",
                                "enum": [
                                  "CURRENT",
                                  "PREFER_EXPLICIT"
                                ],
                                "example": "PREFER_EXPLICIT"
                              },
                              "previous": {
                                "type": "array",
                                "description": "Previous fulfillments.",
                                "items": {
                                  "type": "string",
                                  "example": null
                                }
                              }
                            }
                          }
                        }
                      },
                      "format": {
                        "type": "string",
                        "description": "The format of the product.",
                        "enum": [
                          "digital",
                          "physical",
                          "digital-and-physical"
                        ],
                        "example": "digital"
                      },
                      "taxcode": {
                        "type": "string",
                        "description": "Tax code for the product.",
                        "example": "DC020500"
                      },
                      "taxcodeDescription": {
                        "type": "string",
                        "description": "A description of the tax code.",
                        "example": "Computer software - prewritten - electronically downloaded"
                      },
                      "pricing": {
                        "type": "object",
                        "description": "Pricing details for the product.",
                        "properties": {
                          "trial": {
                            "type": "integer",
                            "description": "The trial period for the product, in days. Only needed if you are creating a subscription.",
                            "example": 2
                          },
                          "interval": {
                            "type": "string",
                            "description": "The billing interval for the product.",
                            "enum": [
                              "week",
                              "month",
                              "year"
                            ],
                            "example": "month"
                          },
                          "intervalLength": {
                            "type": "integer",
                            "description": "Length of the billing interval.",
                            "example": 3
                          },
                          "intervalCount": {
                            "type": "integer",
                            "description": "The number of subscription rebill periods.",
                            "example": 5
                          },
                          "quantityBehavior": {
                            "type": "string",
                            "description": "Defines the behavior for the quantity of the product, such as whether it's allowed to be purchased in different quantities.",
                            "enum": [
                              "allow",
                              "lock",
                              "hide"
                            ],
                            "example": "allow"
                          },
                          "quantityDefault": {
                            "type": "integer",
                            "description": "The default quantity of the product, such as 1 if no other quantity is specified.",
                            "example": 1
                          },
                          "price": {
                            "type": "object",
                            "description": "The price for the product in different currencies.",
                            "properties": {
                              "USD": {
                                "type": "number",
                                "description": "Price in USD.",
                                "example": 10
                              }
                            }
                          },
                          "dateLimitsEnabled": {
                            "type": "boolean",
                            "description": "Whether date limits for the product pricing are enabled.",
                            "example": false
                          },
                          "setupFee": {
                            "type": "object",
                            "description": "One-time setup fee details.",
                            "properties": {
                              "price": {
                                "type": "object",
                                "description": "The price of the setup fee in different currencies.",
                                "properties": {
                                  "USD": {
                                    "type": "number",
                                    "description": "Price in USD.",
                                    "example": 100
                                  }
                                }
                              },
                              "title": {
                                "type": "object",
                                "description": "Title for the setup fee.",
                                "properties": {
                                  "en": {
                                    "type": "string",
                                    "description": "The title of the setup fee in English.",
                                    "example": "One-time Setup Fee Title"
                                  }
                                }
                              }
                            }
                          },
                          "reminderNotification": {
                            "type": "object",
                            "description": "Reminder notification settings.",
                            "properties": {
                              "enabled": {
                                "type": "boolean",
                                "description": "Whether reminder notifications are enabled.",
                                "example": true
                              },
                              "interval": {
                                "type": "string",
                                "description": "Interval for reminder notifications.",
                                "enum": [
                                  "day",
                                  "week",
                                  "month",
                                  "year"
                                ],
                                "example": "week"
                              },
                              "intervalLength": {
                                "type": "integer",
                                "description": "Length of the reminder interval (e.g., 1 week).",
                                "example": 1
                              }
                            }
                          },
                          "overdueNotification": {
                            "type": "object",
                            "description": "Overdue notification settings.",
                            "properties": {
                              "enabled": {
                                "type": "boolean",
                                "description": "Whether overdue notifications are enabled.",
                                "example": true
                              },
                              "interval": {
                                "type": "string",
                                "description": "Interval for overdue notifications.",
                                "enum": [
                                  "day",
                                  "week",
                                  "month",
                                  "year"
                                ],
                                "example": "week"
                              },
                              "intervalLength": {
                                "type": "integer",
                                "description": "Length of the overdue notification interval (e.g., 1 week).",
                                "example": 1
                              },
                              "amount": {
                                "type": "integer",
                                "description": "Total number of times an overdue notification will be sent over the interval length.",
                                "example": 3
                              }
                            }
                          },
                          "cancellation": {
                            "type": "object",
                            "description": "Cancellation details for the product pricing.",
                            "properties": {
                              "interval": {
                                "type": "string",
                                "description": "The cancellation interval.",
                                "enum": [
                                  "day",
                                  "week",
                                  "month"
                                ],
                                "example": "week"
                              },
                              "intervalLength": {
                                "type": "integer",
                                "description": "Length of the cancellation interval.",
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
            }
          }
        }
      }
    }
  }
}
```

Delete a product

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Delete a product

Deletes a product with the given `product_path`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/{product_path}": {
      "parameters": [
        {
          "name": "product_path",
          "in": "path",
          "required": true,
          "description": "Unique identifier to reference a specific product, also known as the product path ID.",
          "example": "premium-laptop",
          "schema": {
            "type": "string"
          }
        }
      ],
      "delete": {
        "summary": "Delete a product",
        "tags": [
          "Products"
        ],
        "description": "Deletes a product with the given `product_path`.",
        "operationId": "DeleteProducts",
        "deprecated": false,
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteProductSuccessResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteProductErrorResponse400"
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
      "DeleteProductSuccessResponse": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to delete the product.",
                  "example": "products.delete"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                },
                "product": {
                  "type": "string",
                  "description": "The product path ID that was deleted.",
                  "example": "basic-laptop"
                }
              }
            }
          }
        }
      },
      "DeleteProductErrorResponse400": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to delete the product.",
                  "example": "products.delete"
                },
                "product": {
                  "type": "string",
                  "description": "The product path ID.",
                  "example": "basic-laptopp"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "properties": {
                    "product": {
                      "type": "string",
                      "description": "The error message indicating the specific problem.",
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

Create or update product offers

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Create or update product offers

Creates product offers or updates existing product offers.

You can configure product offers to enhance the customer shopping experience by suggesting complementary or alternative products.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/offers/{product_path}": {
      "post": {
        "summary": "Create or update product offers",
        "tags": [
          "Products"
        ],
        "description": "Creates product offers or updates existing product offers.\n\nYou can configure product offers to enhance the customer shopping experience by suggesting complementary or alternative products.\n",
        "operationId": "Createorupdateproductoffers",
        "deprecated": false,
        "parameters": [
          {
            "name": "product_path",
            "in": "path",
            "required": true,
            "description": "Unique identifier to reference a specific product, also known as the product path ID.",
            "example": "premium-laptop",
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateorupdateProductOffersRequest"
              },
              "example": {
                "products": [
                  {
                    "product": "premium-keyboard",
                    "offers": [
                      {
                        "type": "cross-sell",
                        "display": {
                          "en": "Discover our complementary products to maximize your results."
                        },
                        "items": [
                          "wireless-mouse",
                          "keyboard-cover"
                        ]
                      },
                      {
                        "type": "addon",
                        "display": {
                          "en": "Enhance your experience with additional features."
                        },
                        "items": [
                          "extra-storage"
                        ]
                      },
                      {
                        "type": "upsell",
                        "display": {
                          "en": "Upgrade to the premium version for more features."
                        },
                        "items": [
                          "premium-laptop"
                        ]
                      },
                      {
                        "type": "downsell",
                        "display": {
                          "en": "Switch to a more affordable version of the product."
                        },
                        "items": [
                          "basic-laptop"
                        ]
                      }
                    ]
                  }
                ]
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
                  "oneOf": [
                    {
                      "$ref": "#/components/schemas/CreateProductOffersResponse"
                    },
                    {
                      "$ref": "#/components/schemas/UpdateProductOffersResponse"
                    }
                  ]
                },
                "examples": {
                  "CreateProductOffersResponseExample": {
                    "summary": "Create Product Offers Response",
                    "value": {
                      "products": [
                        {
                          "product": "premium-keyboard",
                          "action": "products.offers.create",
                          "result": "success"
                        }
                      ]
                    }
                  },
                  "UpdateProductOffersResponseExample": {
                    "summary": "Update Product Offers Response",
                    "value": {
                      "products": [
                        {
                          "product": "premium-keyboard",
                          "action": "products.offers.update",
                          "result": "success"
                        }
                      ]
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CreateorupdateProductOffersError"
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
      "CreateorupdateProductOffersRequest": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The unique identifier for the product.",
                  "example": "premium-keyboard"
                },
                "offers": {
                  "type": "array",
                  "items": {
                    "type": "object",
                    "properties": {
                      "type": {
                        "type": "string",
                        "description": "The type of offer.",
                        "enum": [
                          "addon",
                          "alternatives",
                          "cross-sell",
                          "crossgrade",
                          "downgrade",
                          "downsell",
                          "upsell",
                          "upgrade"
                        ],
                        "example": "cross-sell"
                      },
                      "display": {
                        "type": "object",
                        "properties": {
                          "en": {
                            "type": "string",
                            "description": "The offer description in English.",
                            "example": "Discover our complementary products to maximize your results."
                          }
                        }
                      },
                      "items": {
                        "type": "array",
                        "items": {
                          "type": "string",
                          "description": "Product identifiers for the items in the offer.",
                          "example": "premium-keyboard"
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
      "CreateProductOffersResponse": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product identifier.",
                  "example": "premium-keyboard"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the product.",
                  "example": "products.offers.create"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "UpdateProductOffersResponse": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "product": {
                  "type": "string",
                  "description": "The product identifier.",
                  "example": "premium-keyboard"
                },
                "action": {
                  "type": "string",
                  "description": "The action performed on the product.",
                  "example": "products.offers.update"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                }
              }
            }
          }
        }
      },
      "CreateorupdateProductOffersError": {
        "type": "object",
        "properties": {
          "product": {
            "type": "string",
            "description": "The product identifier.",
            "example": "premium-keyboard"
          },
          "action": {
            "type": "string",
            "description": "The action performed on the product.",
            "example": "products.offers.update"
          },
          "result": {
            "type": "string",
            "description": "The result of the action (e.g., success or error).",
            "example": "error"
          },
          "error": {
            "type": "object",
            "properties": {
              "product": {
                "type": "string",
                "description": "The error message indicating the specific problem.",
                "example": "Product in URL 'premium-keyboard' does not match product in request body 'premium-keyboarddd'"
              }
            }
          }
        }
      }
    }
  }
}
```

List all product prices

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all product prices

Returns all prices for all products, including product discount details.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/price": {
      "get": {
        "summary": "List all product prices",
        "tags": [
          "Products"
        ],
        "description": "Returns all prices for all products, including product discount details.",
        "operationId": "Getallproductsprice",
        "deprecated": false,
        "parameters": [
          {
            "name": "country",
            "in": "query",
            "required": false,
            "description": "The country for which the product price is being requested. This helps filter pricing based on the country's specific pricing rules or regional offers (e.g., \"US\", \"DE\").",
            "schema": {
              "type": "string",
              "example": "US"
            }
          },
          {
            "name": "currency",
            "in": "query",
            "required": false,
            "description": "The currency in which the product price should be displayed. This allows the system to return the price in a specific currency (e.g., \"USD\", \"EUR\").",
            "schema": {
              "type": "string",
              "example": "USD"
            }
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
            "description": "Limits the number of objects returned per page (default limit is 50).",
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
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllProductPriceSuccessResponse"
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
      "GetAllProductPriceSuccessResponse": {
        "type": "object",
        "properties": {
          "page": {
            "type": "integer",
            "description": "The current page number of results.",
            "example": 1
          },
          "limit": {
            "type": "integer",
            "description": "The number of items per page.",
            "example": 1
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results returned.",
            "example": 2
          },
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to retrieve the pricing.",
                  "example": "product.price.getall"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                },
                "product": {
                  "type": "string",
                  "description": "The product path identifier.",
                  "example": "basic-laptop"
                },
                "pricing": {
                  "type": "object",
                  "description": "Pricing details for the product in different regions.",
                  "properties": {
                    "US": {
                      "type": "object",
                      "description": "Pricing details for the US region.",
                      "properties": {
                        "currency": {
                          "type": "string",
                          "description": "The currency used for the price.",
                          "example": "USD"
                        },
                        "price": {
                          "type": "number",
                          "description": "The price of the product.",
                          "example": 600
                        },
                        "display": {
                          "type": "string",
                          "description": "The price display format.",
                          "example": "$600.00"
                        },
                        "quantityDiscount": {
                          "type": "object",
                          "description": "Discounts applied based on quantity.",
                          "properties": {
                            "5": {
                              "type": "object",
                              "description": "Discount applied for buying 5 items.",
                              "properties": {
                                "discountPercent": {
                                  "type": "number",
                                  "description": "The discount percentage.",
                                  "example": 10
                                },
                                "discountValue": {
                                  "type": "number",
                                  "description": "The discount value.",
                                  "example": 60
                                },
                                "discountValueDisplay": {
                                  "type": "string",
                                  "description": "The display format for the discount.",
                                  "example": "$60.00"
                                },
                                "unitPrice": {
                                  "type": "number",
                                  "description": "The price per unit after the discount.",
                                  "example": 540
                                },
                                "unitPriceDisplay": {
                                  "type": "string",
                                  "description": "The display format for the unit price.",
                                  "example": "$540.00"
                                }
                              }
                            }
                          }
                        },
                        "discountReason": {
                          "type": "object",
                          "description": "Reason for the discount.",
                          "properties": {
                            "en": {
                              "type": "string",
                              "description": "The reason for the discount in English.",
                              "example": "New customer"
                            }
                          }
                        },
                        "discountPeriodCount": {
                          "type": "integer",
                          "description": "The duration for which the discount is valid.",
                          "example": 3
                        },
                        "available": {
                          "type": "object",
                          "description": "Availability period for the pricing.",
                          "properties": {
                            "start": {
                              "type": "string",
                              "description": "The start date of the pricing availability.",
                              "example": "2025-01-01"
                            },
                            "end": {
                              "type": "string",
                              "description": "The end date of the pricing availability.",
                              "example": "2025-12-31"
                            }
                          }
                        },
                        "setupFeePrice": {
                          "type": "number",
                          "description": "The setup fee for the product.",
                          "example": 100
                        },
                        "setupFeePriceDisplay": {
                          "type": "string",
                          "description": "The display format for the setup fee.",
                          "example": "$100.00"
                        },
                        "setupFeeReason": {
                          "type": "object",
                          "description": "Reason for the setup fee.",
                          "properties": {
                            "en": {
                              "type": "string",
                              "description": "The reason for the setup fee in English.",
                              "example": "One-time Setup Fee"
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
    }
  }
}
```

Retrieve product offers

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve product offers

Retrieves product offers based on a specific `product_path` and offer type.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/offers/{product_path}": {
      "get": {
        "summary": "Retrieve product offers",
        "tags": [
          "Products"
        ],
        "description": "Retrieves product offers based on a specific `product_path` and offer type.",
        "operationId": "Getalloffersforproductbyoffertype",
        "deprecated": false,
        "parameters": [
          {
            "name": "product_path",
            "in": "path",
            "required": true,
            "description": "Unique identifier to reference a specific product, also known as the product path ID.",
            "example": "basic-laptop",
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "type",
            "in": "query",
            "required": false,
            "description": "A specific type of offer available for the product.",
            "example": "upgrade",
            "schema": {
              "type": "string",
              "enum": [
                "addon",
                "alternatives",
                "cross-sell",
                "crossgrade",
                "downgrade",
                "downsell",
                "upsell",
                "upgrade"
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
                  "$ref": "#/components/schemas/GetAllOffersforProductByOfferTypeSuccessResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetProductsOffersErrorResponse400"
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
      "GetAllOffersforProductByOfferTypeSuccessResponse": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to retrieve the offers.",
                  "example": "products.offers.get"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                },
                "product": {
                  "type": "string",
                  "description": "The product path ID.",
                  "example": "basic-laptop"
                },
                "offers": {
                  "type": "array",
                  "items": {
                    "type": "object",
                    "properties": {
                      "type": {
                        "type": "string",
                        "description": "The type of offer.",
                        "enum": [
                          "addon",
                          "alternatives",
                          "cross-sell",
                          "crossgrade",
                          "downgrade",
                          "downsell",
                          "upsell",
                          "upgrade"
                        ],
                        "example": "cross-sell"
                      },
                      "display": {
                        "type": "object",
                        "properties": {
                          "en": {
                            "type": "string",
                            "description": "The offer description in English.",
                            "example": "Discover our complementary products to maximize your results."
                          }
                        }
                      },
                      "items": {
                        "type": "array",
                        "items": {
                          "type": "string",
                          "description": "Product identifiers for the items in the offer.",
                          "example": "wireless-mouse"
                        }
                      }
                    }
                  },
                  "example": [
                    {
                      "type": "cross-sell",
                      "display": {
                        "en": "Discover our complementary products to maximize your results."
                      },
                      "items": [
                        "wireless-mouse",
                        "keyboard-cover"
                      ]
                    },
                    {
                      "type": "addon",
                      "display": {
                        "en": "Enhance your experience with additional features."
                      },
                      "items": [
                        "extra-storage"
                      ]
                    },
                    {
                      "type": "upsell",
                      "display": {
                        "en": "Upgrade to the premium version for more features."
                      },
                      "items": [
                        "premium-laptop"
                      ]
                    },
                    {
                      "type": "downsell",
                      "display": {
                        "en": "Switch to a more affordable version of the product."
                      },
                      "items": [
                        "basic-laptop"
                      ]
                    }
                  ]
                }
              }
            }
          }
        }
      },
      "GetProductsOffersErrorResponse400": {
        "type": "object",
        "properties": {
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to retrieve the offers.",
                  "example": "products.offers.get"
                },
                "product": {
                  "type": "string",
                  "description": "The product path ID.",
                  "example": "wireless-mousef"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "error"
                },
                "error": {
                  "type": "object",
                  "properties": {
                    "product": {
                      "type": "string",
                      "description": "The error message indicating the specific problem.",
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

Retrieve a product price

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a product price

Retrieves prices for a specific product with the given `product_path`, including product discount details.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Products",
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
      "name": "Products",
      "description": "Create, list, retrieve, preview, and delete products.\n"
    }
  ],
  "paths": {
    "/products/price/{product_path}": {
      "get": {
        "summary": "Retrieve a product price",
        "tags": [
          "Products"
        ],
        "description": "Retrieves prices for a specific product with the given `product_path`, including product discount details.",
        "operationId": "Getspecificproductprice",
        "deprecated": false,
        "parameters": [
          {
            "name": "product_path",
            "in": "path",
            "required": true,
            "description": "Unique identifier to reference a specific product, also known as the product path ID.",
            "example": "basic-laptop",
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "country",
            "in": "query",
            "required": false,
            "description": "The country for which the product price is being requested. This helps filter pricing based on the country's specific pricing rules or regional offers (e.g., \"US\", \"DE\").",
            "schema": {
              "type": "string",
              "example": "US"
            }
          },
          {
            "name": "currency",
            "in": "query",
            "required": false,
            "description": "The currency in which the product price should be displayed. This allows the system to return the price in a specific currency (e.g., \"USD\", \"EUR\").",
            "schema": {
              "type": "string",
              "example": "USD"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetProductPriceSuccessResponse"
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
      "GetProductPriceSuccessResponse": {
        "type": "object",
        "properties": {
          "page": {
            "type": "integer",
            "description": "The current page number of results.",
            "example": 1
          },
          "limit": {
            "type": "integer",
            "description": "The number of items per page.",
            "example": 50
          },
          "products": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed to retrieve the pricing.",
                  "example": "product.price.get"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
                  "example": "success"
                },
                "product": {
                  "type": "string",
                  "description": "The product path identifier.",
                  "example": "basic-laptop"
                },
                "pricing": {
                  "type": "object",
                  "description": "Pricing details for the product in different regions.",
                  "properties": {
                    "US": {
                      "type": "object",
                      "description": "Pricing details for the US region.",
                      "properties": {
                        "currency": {
                          "type": "string",
                          "description": "The currency used for the price.",
                          "example": "USD"
                        },
                        "price": {
                          "type": "number",
                          "description": "The price of the product.",
                          "example": 600
                        },
                        "display": {
                          "type": "string",
                          "description": "The price display format.",
                          "example": "$600.00"
                        },
                        "quantityDiscount": {
                          "type": "object",
                          "description": "Discounts applied based on quantity.",
                          "properties": {
                            "5": {
                              "type": "object",
                              "description": "Discount applied for buying 5 items.",
                              "properties": {
                                "discountPercent": {
                                  "type": "number",
                                  "description": "The discount percentage.",
                                  "example": 10
                                },
                                "discountValue": {
                                  "type": "number",
                                  "description": "The discount value.",
                                  "example": 60
                                },
                                "discountValueDisplay": {
                                  "type": "string",
                                  "description": "The display format for the discount.",
                                  "example": "$60.00"
                                },
                                "unitPrice": {
                                  "type": "number",
                                  "description": "The price per unit after the discount.",
                                  "example": 540
                                },
                                "unitPriceDisplay": {
                                  "type": "string",
                                  "description": "The display format for the unit price.",
                                  "example": "$540.00"
                                }
                              }
                            }
                          }
                        },
                        "discountReason": {
                          "type": "object",
                          "description": "Reason for the discount.",
                          "properties": {
                            "en": {
                              "type": "string",
                              "description": "The reason for the discount in English.",
                              "example": "New customer"
                            }
                          }
                        },
                        "discountPeriodCount": {
                          "type": "integer",
                          "description": "The duration for which the discount is valid.",
                          "example": 3
                        },
                        "available": {
                          "type": "object",
                          "description": "Availability period for the pricing.",
                          "properties": {
                            "start": {
                              "type": "string",
                              "description": "The start date of the pricing availability.",
                              "example": "2025-01-01"
                            },
                            "end": {
                              "type": "string",
                              "description": "The end date of the pricing availability.",
                              "example": "2025-12-31"
                            }
                          }
                        },
                        "setupFeePrice": {
                          "type": "number",
                          "description": "The setup fee for the product.",
                          "example": 100
                        },
                        "setupFeePriceDisplay": {
                          "type": "string",
                          "description": "The display format for the setup fee.",
                          "example": "$100.00"
                        },
                        "setupFeeReason": {
                          "type": "object",
                          "description": "Reason for the setup fee.",
                          "properties": {
                            "en": {
                              "type": "string",
                              "description": "The reason for the setup fee in English.",
                              "example": "One-time Setup Fee"
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
    }
  }
}
```
