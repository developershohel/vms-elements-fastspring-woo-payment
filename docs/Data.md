Generate subscription report

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate subscription report

Generates a subscription report.

You can apply filters on date range, country name, product name, and product path. For each filtered variable, you can define the columns you want to include in your report and how to group results.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/subscription": {
      "post": {
        "summary": "Generate subscription report",
        "tags": [
          "Data"
        ],
        "operationId": "GenerateSubscriptionReport",
        "deprecated": false,
        "description": "Generates a subscription report.\n\nYou can apply filters on date range, country name, product name, and product path. For each filtered variable, you can define the columns you want to include in your report and how to group results.\n",
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/GenerateSubscriptionReportRequest"
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
                      "$ref": "#/components/schemas/PostSubscriptionSyncResponse"
                    },
                    {
                      "$ref": "#/components/schemas/PostSubscriptionAsyncResponse"
                    }
                  ]
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PostSubscriptionResponseBad"
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
      "GenerateSubscriptionReportRequest": {
        "type": "object",
        "description": "Request body schema to generate a subscription report, including filtering criteria, columns, grouping, pagination, and notification options.",
        "properties": {
          "filter": {
            "type": "object",
            "description": "Filtering criteria for the subscription report, including date ranges, product filters, country codes, and segments.",
            "properties": {
              "startDate": {
                "type": "string",
                "format": "date",
                "description": "Start date (YYYY-MM-DD) for the reporting range.",
                "example": "2025-01-01"
              },
              "endDate": {
                "type": "string",
                "format": "date",
                "description": "End date (YYYY-MM-DD) for the reporting range.",
                "example": "2025-12-31"
              },
              "syncDate": {
                "type": "string",
                "format": "date",
                "description": "Last update reference date (YYYY-MM-DD).",
                "example": "2024-12-31"
              },
              "countryISO": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of 2-letter country codes to filter the report by.",
                "example": "US"
              },
              "productNames": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of product names to filter the report by.",
                "example": "primary subscription (addon-subscription)"
              },
              "productPaths": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of product paths to filter the report by.",
                "example": "primary subscription (addon-subscription)"
              },
              "segments": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of segment identifiers to filter the report by.",
                "example": "demographic"
              }
            }
          },
          "reportColumns": {
            "type": "array",
            "description": "Columns to include in the subscription report:\n\n| Column                     | Description                                                                                                     |\n|----------------------------|-----------------------------------------------------------------------------------------------------------------|\n| `activations`             | Number of activations in the selected period.                                                                    |\n| `arr`                     | Cumulative Annual Run Rate until endDate.                                                                        |\n| `average_mrr`             | Cumulative Average MRR until endDate.                                                                            |\n| `buyer_email`             | Buyer’s email address.                                                                                           |\n| `buyer_id`                | Unique ID for the buyer.                                                                                         |\n| `cancellations`           | Number of cancellations in the selected period.                                                                  |\n| `chargeback_true_false`   | `true` if the order was charged back, `false` if successful.                                                     |\n| `churn_type`              | Churn type if canceled.                                                                                          |\n| `company_id`              | Seller's unique ID.                                                                                              |\n| `company_name`            | Seller's name.                                                                                                   |\n| `country_iso`             | 2-letter ISO country code.                                                                                       |\n| `country_name`            | English country name.                                                                                            |\n| `coupon`                  | The coupon code used.                                                                                            |\n| `customer_churn`          | Subscriber loss over prior 30 days / active customers 30 days ago.                                              |\n| `discount`                | Discount amount.                                                                                                 |\n| `driving_offer_type`      | e.g., cross-sell, upsell, bundle, addon, etc.                                                                    |\n| `driving_product_path`    | Parent product path.                                                                                             |\n| `lifetime_value`          | Average MRR / customer churn.                                                                                    |\n| `mrr`                     | Cumulative Monthly Recurring Revenue until endDate.                                                               |\n| `mrr_decrease`            | MRR decrease.                                                                                                    |\n| `mrr_downgrade`           | MRR from downgrades in the selected period.                                                                      |\n| `mrr_growth_rate`         | (current MRR - MRR 30 days ago) / (MRR 30 days ago).                                                              |\n| `mrr_increase`            | MRR increase in the selected period.                                                                             |\n| `mrr_paused`              | MRR from pauses in the selected period.                                                                          |\n| `mrr_resumed`             | MRR from resumes in the selected period.                                                                         |\n| `mrr_upgrade`             | MRR from upgrades in the selected period.                                                                        |\n| `new_subscribers`         | Number of new subscribers in the selected period.                                                                |\n| `occurred_date`           | Date of the subscription.                                                                                        |\n| `order_id`                | Order ID.                                                                                                        |\n| `product_display_name`    | Display name of the product.                                                                                     |\n| `product_id`              | Internal product ID.                                                                                             |\n| `product_name`            | Product name.                                                                                                    |\n| `product_path`            | Primary key for the product.                                                                                     |\n| `purchase_type`           | `first` or `recurring` (renewal).                                                                                |\n| `return_true_false`       | `true` if returned, `false` otherwise.                                                                           |\n| `revenue_churn`           | (MRR decrease) / (MRR 30 days ago).                                                                              |\n| `segment`                 | Group label for a product/country set.                                                                           |\n| `store_id`                | ID of the store.                                                                                                 |\n| `store_name`              | Name of the store.                                                                                               |\n| `subscriber_loss`         | Subscribers lost in the selected period.                                                                         |\n| `subscribers`             | Cumulative subscribers until endDate.                                                                            |\n| `subscription_id`         | Unique subscription identifier.                                                                                  |\n| `subscription_period`     | Length of the subscription.                                                                                      |\n| `subscription_period_end` | End date of the subscription period.                                                                             |\n| `subscription_period_start` | Start date of the subscription period.                                                                         |\n| `subscription_quantity`   | Subscription units from order creation.                                                                          |\n| `subscription_start_date` | The subscription’s start date.                                                                                   |\n| `subscription_status`     | The subscription status.                                                                                         |\n| `subscription_true_false` | `true` if subscription product, `false` for a one-time product.                                                  |\n| `subscriptions`           | Cumulative subscriptions until endDate.                                                                          |\n| `sync_date`               | The last sync date for the data.                                                                                 |\n| `transaction_currency`    | Currency of the order.                                                                                           |\n| `transaction_date`        | Date of the order.                                                                                               |\n| `transaction_day`         | Day-month of the transaction.                                                                                    |\n| `transaction_month`       | Month of the transaction.                                                                                        |\n| `transaction_time_utc`    | UTC time of the transaction.                                                                                     |\n| `transaction_year`        | Year of the transaction.                                                                                         |\n",
            "items": {
              "type": "string"
            },
            "example": [
              "activations",
              "arr",
              "average_mrr"
            ]
          },
          "groupBy": {
            "type": "array",
            "description": "Fields used to group or aggregate the data:\n\n| Field                      | Description                                    |\n|----------------------------|------------------------------------------------|\n| `buyer_email`             | Buyer’s email address.                         |\n| `buyer_id`                | Unique ID for the buyer’s orders.             |\n| `company_id`              | The seller’s unique ID.                       |\n| `company_name`            | The seller’s name.                            |\n| `country_iso`             | 2-letter ISO country code.                    |\n| `country_name`            | English name for the country.                 |\n| `coupon`                  | The coupon code used.                         |\n| `chargeback_true_false`   | `true` if order charged back, `false` if not. |\n| `churn_type`              | Churn type when canceled.                     |\n| `discount`                | The discount amount.                          |\n| `driving_offer_type`      | e.g., cross-sell, upsell, addon, etc.         |\n| `driving_product_path`    | The parent product path.                      |\n| `occurred_date`           | The date of the subscription.                |\n| `order_id`                | The order ID.                                 |\n| `product_display_name`    | Display name of the product.                  |\n| `product_id`              | Internal product ID.                          |\n| `product_name`            | Product name.                                 |\n| `product_path`            | Primary product path.                         |\n| `purchase_type`           | `first` or `recurring` (renewal).            |\n| `return_true_false`       | `true` if returned, `false` otherwise.        |\n| `segment`                 | Groups products/countries with shared traits. |\n| `store_id`                | The store’s ID.                               |\n| `store_name`              | The store’s name.                             |\n| `subscription_id`         | Unique subscription identifier.              |\n| `subscription_period`     | The subscription length.                      |\n| `subscription_period_end` | End date of the subscription period.          |\n| `subscription_period_start`| Start date of the subscription period.       |\n| `subscription_start_date` | The subscription start date.                  |\n| `subscription_status`     | The subscription status.                      |\n| `subscription_true_false` | `true` if subscription product, `false` if not.|\n| `sync_date`               | Last sync date.                               |\n| `transaction_currency`    | The currency of the order.                    |\n| `transaction_date`        | The date of the order.                        |\n| `transaction_day`         | Day-month of the subscription transaction.    |\n| `transaction_month`       | Month of the transaction.                     |\n| `transaction_year`        | Year of the transaction.                      |\n",
            "items": {
              "type": "string"
            },
            "example": [
              "buyer_email",
              "buyer_id",
              "company_id",
              "country_iso",
              "product_path",
              "subscription_id",
              "sync_date"
            ]
          },
          "pageCount": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "maximum": 1000,
            "description": "Number of records to return per page.",
            "example": 30
          },
          "pageNumber": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "description": "Specifies the page number of results to be returned.",
            "example": 1
          },
          "async": {
            "type": "boolean",
            "description": "Indicates if the report generation was requested asynchronously.",
            "example": false
          },
          "notificationEmails": {
            "type": "array",
            "description": "List of emails to notify upon job completion.",
            "items": {
              "type": "string"
            },
            "example": "jane.doe@example.com"
          }
        }
      },
      "PostSubscriptionSyncResponse": {
        "title": "Subscription Sync Response",
        "type": "object",
        "properties": {
          "report": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "arr": {
                  "type": "number",
                  "description": "Cumulative Annual Run Rate until the endDate selected.",
                  "example": 1080
                },
                "subscriptions": {
                  "type": "number",
                  "description": "Cumulative number of subscriptions until the endDate selected.",
                  "example": 1
                },
                "subscriber_loss": {
                  "type": "number",
                  "description": "Subscribers lost in the selected period of time.",
                  "example": 0
                },
                "customer_churn": {
                  "type": "number",
                  "description": "Sum of subscriber loss over the prior 30 days divided by the active customers 30 days ago.",
                  "example": 0
                },
                "subscribers": {
                  "type": "number",
                  "description": "Cumulative number of subscribers until the endDate selected.",
                  "example": 1
                },
                "mrr_growth_rate": {
                  "type": "number",
                  "description": "(MRR for the current day - MRR 30 days ago)/ MRR 30 days ago.",
                  "example": 0
                },
                "new_subscribers": {
                  "type": "number",
                  "description": "New subscribers in the selected period of time.",
                  "example": 0
                },
                "average_mrr": {
                  "type": "number",
                  "description": "Cumulative Average Monthly Recurring Revenue until the endDate selected.",
                  "example": 90
                },
                "mrr": {
                  "type": "number",
                  "description": "Cumulative Monthly Recurring Revenue until the endDate selected.",
                  "example": 90
                },
                "product_name": {
                  "type": "string",
                  "description": "The product name.",
                  "example": "primary subscription (addon-subscription)"
                },
                "occurred_date": {
                  "type": "string",
                  "format": "date",
                  "description": "Date of the subscription.",
                  "example": "2025-01-01"
                },
                "cancellations": {
                  "type": "integer",
                  "description": "Number of cancellations in the selected period of time.",
                  "example": 0
                },
                "revenue_churn": {
                  "type": "number",
                  "description": "Sum of MRR decrease over the prior 30 days divided by the MRR 30 days ago.",
                  "example": 0
                },
                "lifetime_value": {
                  "type": "number",
                  "description": "The AVG MRR divided by the customer churn (percentage).",
                  "example": 0
                },
                "mrr_decrease": {
                  "type": "number",
                  "description": "MRR decrease in the selected period.",
                  "example": 0
                },
                "mrr_increase": {
                  "type": "number",
                  "description": "MRR increase in the selected period.",
                  "example": 0
                },
                "site_id": {
                  "type": "string",
                  "description": "An identifier for the store or site associated with the quote/report.",
                  "example": "AbC1De2FGhi"
                },
                "country_name": {
                  "type": "string",
                  "description": "The country name.",
                  "example": "Uruguay"
                },
                "activations": {
                  "type": "number",
                  "description": "Number of activations in the selected period of time.",
                  "example": 0
                }
              }
            }
          },
          "request": {
            "type": "object",
            "properties": {
              "filter": {
                "type": "object",
                "description": "Filter conditions that were applied to generate the report.",
                "properties": {
                  "startDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Start date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-01-01"
                  },
                  "endDate": {
                    "type": "string",
                    "format": "date",
                    "description": "End date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-12-31"
                  },
                  "productNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product names to filter the report by.",
                    "example": [
                      "primary subscription (addon-subscription)"
                    ]
                  },
                  "countryNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of country names to filter the report by.",
                    "example": [
                      "Uruguay"
                    ]
                  }
                }
              },
              "reportColumns": {
                "type": "array",
                "description": "Columns to include in the subscription report (if specified).",
                "items": {
                  "type": "string"
                },
                "example": [
                  "arr",
                  "subscriptions",
                  "subscriber_loss",
                  "mrr_add_on",
                  "product_name"
                ]
              },
              "pageCount": {
                "type": "integer",
                "format": "int32",
                "description": "Number of records to return per page.",
                "example": 30
              },
              "pageNumber": {
                "type": "integer",
                "format": "int32",
                "description": "Specifies the page number of results to be returned.",
                "example": 1
              },
              "async": {
                "type": "boolean",
                "description": "Indicates if the report generation was requested asynchronously.",
                "example": false
              },
              "notificationEmails": {
                "type": "array",
                "description": "List of emails to notify upon job completion.",
                "items": {
                  "type": "string"
                },
                "example": [
                  "jane.doe@example.com"
                ]
              }
            }
          }
        }
      },
      "PostSubscriptionAsyncResponse": {
        "title": "Subscription Async Response",
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the asynchronous job generating the subscription report.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "name": {
            "type": "string",
            "description": "Name assigned to the subscription report job.",
            "example": "SubscriptionReport"
          },
          "status": {
            "type": "string",
            "description": "Current status of the asynchronous report generation (e.g., PROCESSING, COMPLETED, FAILED).",
            "example": "PROCESSING"
          },
          "request": {
            "type": "object",
            "properties": {
              "filter": {
                "type": "object",
                "properties": {
                  "startDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Start date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-01-01"
                  },
                  "endDate": {
                    "type": "string",
                    "format": "date",
                    "description": "End date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-12-31"
                  },
                  "productNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product names to filter the report by.",
                    "example": [
                      "primary subscription (addon-subscription)"
                    ]
                  },
                  "countryNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of country names to filter the report by.",
                    "example": [
                      "Uruguay"
                    ]
                  }
                }
              },
              "reportColumns": {
                "type": "array",
                "description": "Columns to include in the subscription report (if specified).",
                "items": {
                  "type": "string"
                },
                "example": [
                  "arr",
                  "subscriptions",
                  "subscriber_loss",
                  "mrr_add_on",
                  "product_name"
                ]
              },
              "pageCount": {
                "type": "integer",
                "format": "int32",
                "description": "Number of records to return per page.",
                "example": 30
              },
              "pageNumber": {
                "type": "integer",
                "format": "int32",
                "description": "Specifies the page number of results to be returned.",
                "example": 1
              },
              "async": {
                "type": "boolean",
                "description": "Indicates if the report generation was requested asynchronously.",
                "example": true
              },
              "notificationEmails": {
                "type": "array",
                "description": "List of emails to notify upon job completion.",
                "items": {
                  "type": "string"
                },
                "example": [
                  "jane.doe@example.com"
                ]
              }
            }
          }
        }
      },
      "PostSubscriptionResponseBad": {
        "type": "object",
        "description": "Schema for an error response indicating a bad request or other invalid state.",
        "properties": {
          "status": {
            "type": "string",
            "description": "HTTP status code.",
            "example": "BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "Timestamp indicating when the error occurred.",
            "example": "27-12-2024 05:30:57"
          },
          "id": {
            "type": "string",
            "description": "A unique identifier for this particular error instance.",
            "example": "ABCDEFGH1IJKLMNOPQRSTUV23W"
          },
          "message": {
            "type": "string",
            "description": "An error message describing the issue.",
            "example": "Invalid column for grouping"
          },
          "details": {
            "type": "string",
            "nullable": true,
            "description": "Additional details or context about the error, if available.",
            "example": null
          },
          "errors": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A code representing the specific error condition.",
                  "example": "invalid.column"
                },
                "field": {
                  "type": "string",
                  "description": "The name of the field or property involved in the error, if applicable.",
                  "example": "item_id"
                },
                "message": {
                  "type": "string",
                  "description": "A description of this specific error.",
                  "example": "Invalid column for grouping"
                },
                "rejectedValue": {
                  "type": "string",
                  "nullable": true,
                  "description": "The value that was rejected or caused the error, if any.",
                  "example": "item_id"
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

Generate revenue report

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate revenue report

Generates a revenue report.

You can apply filters on date range, country name, product name, and product path. For each filtered variable, you can define the columns you want to include in your report and how to group results.  


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/revenue": {
      "post": {
        "summary": "Generate revenue report",
        "tags": [
          "Data"
        ],
        "operationId": "GenerateRevenueReport",
        "deprecated": false,
        "description": "Generates a revenue report.\n\nYou can apply filters on date range, country name, product name, and product path. For each filtered variable, you can define the columns you want to include in your report and how to group results.  \n",
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/GenerateRevenueReportRequest"
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
                      "$ref": "#/components/schemas/PostRevenueSyncResponse"
                    },
                    {
                      "$ref": "#/components/schemas/PostRevenueAsyncResponse"
                    }
                  ]
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PostRevenueResponseBad"
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
      "GenerateRevenueReportRequest": {
        "type": "object",
        "properties": {
          "filter": {
            "type": "object",
            "properties": {
              "startDate": {
                "type": "string",
                "format": "date",
                "description": "Start date (YYYY-MM-DD) for the reporting range.",
                "example": "2025-01-01"
              },
              "endDate": {
                "type": "string",
                "format": "date",
                "description": "End date (YYYY-MM-DD) for the reporting range.",
                "example": "2025-12-31"
              },
              "countryISO": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of 2-letter country codes to filter the report by.",
                "example": [
                  "CO"
                ]
              },
              "productNames": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of product names to filter the report by.",
                "example": [
                  "toxin product (toxin-product)"
                ]
              },
              "productPaths": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of product paths to filter the report by.",
                "example": [
                  "toxin-product"
                ]
              },
              "segments": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "List of segment identifiers to filter the report by.",
                "example": [
                  "demographic"
                ]
              },
              "syncDate": {
                "type": "string",
                "format": "date",
                "description": "Last update reference date (YYYY-MM-DD).",
                "example": "2024-12-31"
              }
            }
          },
          "reportColumns": {
            "type": "array",
            "description": "Columns to include in the revenue report:\n\n| Column Name                     | Description                                                                                                                                                                                                                                                                                                                                                                                              |\n|---------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|\n| `Buyer_Email`                   | Email used by the buyer for the transaction(s)                                                                                                                                                                                                                                                                                                                                                          |\n| `Buyer_ID`                      | Unique ID allocated to buyer in FastSpring's internal system                                                                                                                                                                                                                                                                                                                                             |\n| `Chargeback_True_False`         | This is to identify if there was a chargeback on a given transaction. If the value is \"True\", there is a chargeback on the transaction and if the value is \"False\" there is no chargeback on the transaction.                                                                                                                                                                                                         |\n| `Company_ID`                    | The company ID as used by businesses on FastSpring system. This is the ID used to access the app.fastspring.com and get to the landing page with all the stores(SITE_IDs) within a company.                                                                                                                                                                                                                     |\n| `Company_Name`                  | Name of the business as registered with FastSpring                                                                                                                                                                                                                                                                                                                                                         |\n| `Country_ISO`                   | 2 letter code that represent a country. This is used to capture the country in buyer's address                                                                                                                                                                                                                                                                                                               |\n| `Country_Name`                  | Name of the country in buyer's address for a given transaction record                                                                                                                                                                                                                                                                                                                                       |\n| `Coupon`                        | \"Name of the coupon applied on a given transaction.<br>Example: As this field can be used to group the records, the reports with transaction grouped by COUPON along with INCOME can give insights into how much revenue each coupon is driving for the business.\"                                                                                                                                           |\n| `Digital_Backup_Fulfillment_Fee`| If the business charges a fee for digital backup of the software from the buyer, this field has the amount in local currency of transaction.                                                                                                                                                                                                                                                                        |\n| `Digital_Backup_Fulfillment_Fee_in_USD`| If the business charges a fee for digital backup of the product from the buyer, this field has the amount in USD.                                                                                                                                                                                                                                                                                |\n| `Digital_Fulfillment_Fee`       | If the business charges a fee for fulfillment (usually applies to perpetual license products), this field has the amount in local currency.                                                                                                                                                                                                                                                              |\n| `Digital_Fulfillment_Fee_in_USD`| If the business charges a fee for fulfillment (usually applies to perpetual license products), this field has the amount in USD.                                                                                                                                                                                                                                                                          |\n| `Discount`                      | The name of the discount configured for the product(s) is stored in this field.                                                                                                                                                                                                                                                                                                                           |\n| `Driving_Offer_Type`            | The type of offer that is associated with the parent product in an order is captured in this field. The exhaustive list of offer types is as follows:<br>cross-sell<br>upsell<br>options<br>bundle<br>configuration<br>one_time_fee<br>addon                                                                                                                                                                    |\n| `Driving_Product_Path`          | The parent product with other products tagged in offers is captured here. The product path differs from the product name as the product path is used in the backend by FastSpring system to identify the products. But they both represent the same entity, the products in the catalog.                                                                                                                                      |\n| `Fixed_Fee`                     | The fee FastSpring charges on a transaction in local currency.<br>Note: Use this field for account reconciliation purposes if the payout currency for a given transaction is a currency other than USD.                                                                                                                                                                                                             |\n| `Fixed_Fee_in_USD`              | The fee FastSpring charges on a transaction in USD.<br>Note: Use this field for account reconciliation purposes if the payout currency for a given transaction is USD.                                                                                                                                                                                                                                    |\n| `Income`                        | Revenue the business makes on a transaction before tax, including FastSpring Fee in local currency.<br>Note: Use this field for account reconciliation purposes if the payout currency for a given transaction is a currency other than USD.                                                                                                                                                                          |\n| `Income_in_USD`                 | Revenue the business makes on a transaction before tax, including FastSpring Fee in USD.<br>Note: Use this field for account reconciliation purposes if the payout currency for a given transaction is USD.                                                                                                                                                                                                 |\n| `Item_ID`                       | Unique ID for item(s) in a given order. In a B2B context, there could be large volumes of products within an order and this can help in identifying an item in such large orders.                                                                                                                                                                                                                           |\n| `Order_ID`                      | Unique ID for a given transaction. Order and transaction are used interchangeably here and any unique order or transaction can be identified using Order_ID.                                                                                                                                                                                                                                              |\n| `Physical_Backup_Fulfillment_Fee`| If the business charges a fee for the physical fulfillment of a product (usually CD/Drive with perpetual license software), the fee amount in local currency is reported here.                                                                                                                                                                                                                                |\n| `Physical_Backup_Fulfillment_Fee_in_USD`| If the business charges a fee for physical fulfillment of a product (usually CD/Drive with perpetual license software), the fee amount in USD is reported here.                                                                                                                                                                                                                                           |\n| `Product_Display_Name`          | The product name displayed to the customers on the checkout page.                                                                                                                                                                                                                                                                                                                                    |\n| `Product_ID`                    | Unique ID for a product.                                                                                                                                                                                                                                                                                                                                                                      |\n| `Product_Name`                  | The product name.                                                                                                                                                                                                                                                                                                                                                                            |\n| `Product_Path`                  | The primary key for the product.                                                                                                                                                                                                                                                                                                                                                                |\n| `Purchase_Type`                 | For subscriptions, this field can differentiate between first bill vs recurring bill, meaning when a customer pays for the first time, it is tagged \"First\" in this field. From the next payment for rest of the subscription's active lifetime, this will be \"Recurring\" as the customer is paying for the subscription service at fixed intervals (monthly, quarterly etc.).                                 |\n| `Return_Fee`                    | If a purchase has been returned and there is a fee for returning the product, it will be captured in this field in the local currency of the transaction. This is a transaction-level fee.                                                                                                                                                                                                             |\n| `Return_Fee_in_USD`             | If a purchase has been returned and there is a fee for returning the product, it will be captured in this field in USD. This is a transaction-level fee.                                                                                                                                                                                                                                             |\n| `Return_True_False`             | This field can be used to know if a transaction has been returned or not. For transactions where the item(s) are returned, this field will have a \"True\" value and if the transaction did not have problems and went through successfully, this will have a \"False\".                                                                                                                                                          |\n| `Segment`                       | If transactions pass value for reserved Key in Order tag (JSON Key value pair passed in session) FS_Segmentation (Not case sensitive), the data can be grouped.<br>Eg: Order tag with Key: Value in order tag with _FS_Segmentation: B2B can be used to group all the other fields for just B2B segment.                                                                                                                     |\n| `Store_Chargeback_Fee`          | If a transaction was charged back, there is a chargeback fee applied to the transaction. This field captures the value of the chargeback fee on a given transaction. This is a fixed fee charged on a chargeback transaction irrespective of the value of the order.                                                                                                                                               |\n| `Store_ID`                      | A unique identifier associated with a store within a company. If a business has multiple stores for different purposes (For example, a store for B2C business vs another store for B2B business), this can be used to filter all the transactions for a specific store.                                                                                                                                          |\n| `Store_Name`                    | In the hierarchy, a company can have multiple stores under it. This field stores the Store name. The usual purpose of this hierarchy is to separate the categories of business within a company. For example, a business may want to have a separate store for B2C business vs B2B business.                                                                                                                    |\n| `Subscription_Period`           | For a subscription product, the length of the subscription is captured in this field. Conventionally many subscriptions have \"1 Month\", \"1 year\", \"On Demand\" etc.                                                                                                                                                                                                                                   |\n| `Subscription_Period_End`       | The subscription has recurring payment events so there is a start date and an end date to each payment cycle. This field captures the end date of a subscription cycle. At the end of the cycle, the customer is due to make the payment for using the software for the next period.                                                                                                                                   |\n| `Subscription_Period_Start`     | The subscription has recurring payment events so there is a start date and an end date to each payment cycle. This field captures the start date of a subscription cycle. At the beginning of the cycle, the customer has paid for using the software until the end of the subscription period.                                                                                                                    |\n| `Subscription_Start_Date`       | The date on which a given subscription started                                                                                                                                                                                                                                                                                                                                                    |\n| `Subscription_Status`           | The status of subscription captures what phase in a subscription lifecycle a given subscription is. Below is the exhaustive list of possible values for Subscription status:<br><br>Active (Subscription is active meaning the customer can access the software and is willing to pay for subscription service for the upcoming billing cycle)<br><br>Canceled (The customer has clicked on the Cancel button and the customer does not want to pay for the software from the upcoming billing cycle)<br><br>Canceled Nonpayment (The customer has not voluntarily canceled the subscription. However the system was not able to charge the customer, AKA Involuntary churn resulting from payment decline for a variety of reasons)<br><br>Undefined (For managed subscriptions, the billing dates are not defined. As these subscriptions are billed based on the configuration on the seller's end, the status is not defined on FastSpring system. Classic use case being usage-based pricing where the customer has to pay not based on the subscription period but based on usage of the software)<br><br>Completed (For Subscriptions that have ended) |\n| `Subscription_True_False`       | This field helps to identify if a product is a subscription product with recurring billing or a one-time purchase perpetual license product. If the field has a \"True\" value, the product is a subscription product and if the value is \"False\" then the product is a one-time purchase product.                                                                                                                                       |\n| `Tax`                           | Tax on the transaction in local currency                                                                                                                                                                                                                                                                                                                                                        |\n| `Tax_Fee`                       | FastSpring Fee on the tax for a given transaction in local currency                                                                                                                                                                                                                                                                                                                              |\n| `Tax_Fee_in_USD`                | FastSpring Fee on the tax for a given transaction in USD                                                                                                                                                                                                                                                                                                                                         |\n| `Tax_in_USD`                    | Tax on the transaction in USD                                                                                                                                                                                                                                                                                                                                                                  |\n| `Transaction_Amount`            | Revenue on a transaction excluding FastSpring fee and Tax in local currency                                                                                                                                                                                                                                                                                                                      |\n| `Transaction_Amount_in_USD`     | Revenue on a transaction excluding FastSpring fee and Tax in USD                                                                                                                                                                                                                                                                                                                                 |\n| `Transaction_Currency`          | Currency in which the transaction occurred. This field can be relevant for reconciliation if the business receives a payout from FastSpring in more than one currency.                                                                                                                                                                                                                                  |\n| `Transaction_Date`              | The date stamp on which the transaction occurred                                                                                                                                                                                                                                                                                                                                                  |\n| `Transaction_Day`               | Day-month of the subscription transaction                                                                                                                                                                                                                                                                                                                                                        |\n| `Transaction_Fee`               | FastSpring fee on the given transaction in local currency                                                                                                                                                                                                                                                                                                                                         |\n| `Transaction_Fee_in_USD`        | FastSpring fee on the given transaction in USD                                                                                                                                                                                                                                                                                                                                                    |\n| `Transaction_Item_Count`        | Number of items in a given order/transaction. Order and transaction are used interchangeably here and any unique order or transaction can be identified using Order_ID                                                                                                                                                                                                                           |\n| `Transaction_Month`             | The month in which the transaction occurred. The values in the field are in YYYY-MM format and capture the year and month.                                                                                                                                                                                                                                                                      |\n| `Transaction_Rate`              | The percentage of revenue that FastSpring charges the business is captured at a line item level. This can be used to reconcile the FastSpring fee charged at the line item level.                                                                                                                                                                                                                 |\n| `Transaction_Time_UTC`          | Timestamp of when the transaction occurred in UTC timezone                                                                                                                                                                                                                                                                                                                                       |\n| `Transaction_Year`              | The year of the transaction (YYYY)                                                                                                                                                                                                                                                                                                                                                             |\n| `Grand_Total_In_USD`            | The grand total of the whole order in USD                                                                                                                                                                                                                                                                                                                                                       |\n| `syncDate`                      | When reading new data, the last read date.<br>This field can be used to sync up your DB with FastSpring’s data APIs                                                                                                                                                                                                                                                                               |\n| `countryISO`                    | The country code in 2 letter ISO format                                                                                                                                                                                                                                                                                                                                                          |\n| `Product_Count`                 | The number of unique Product_IDs purchased within a given order. For example, if an order has 10 products in total with 5 units of Product_A and 5 units of Product_B, the value in this field will be 2 because there were 2 unique products in the order.                                                                                                                                                      |\n| `Product_Units`                 | The number of products purchased in a given order. Following the above example, where an order has 10 products in total with 5 units of Product_A and 5 units of Product_B, this field will have a value of 10, because the total number of units irrespective of products was 10.                                                                                                                            |\n",
            "items": {
              "type": "string"
            },
            "example": [
              "buyer_id",
              "buyer_email",
              "chargeback_true_false",
              "company_id",
              "company_name",
              "country_iso",
              "country_name",
              "countryiso",
              "coupon",
              "digital_backup_fulfillment_fee",
              "digital_backup_fulfillment_fee_in_usd",
              "digital_fulfillment_fee",
              "digital_fulfillment_fee_in_usd",
              "discount",
              "driving_offer_type",
              "driving_product_path",
              "end_date",
              "enddate",
              "fixed_fee",
              "fixed_fee_in_usd",
              "grand_total_in_usd",
              "income",
              "income_in_usd",
              "item_id",
              "order_count",
              "order_id",
              "physical_backup_fulfillment_fee",
              "physical_backup_fulfillment_fee_in_usd",
              "product_count",
              "product_display_name",
              "product_id",
              "product_name",
              "product_path",
              "product_units",
              "productpath",
              "purchase_type",
              "return_fee",
              "return_fee_in_usd",
              "return_true_false",
              "segment",
              "start_date",
              "startdate",
              "store_chargeback_fee",
              "store_id",
              "store_name",
              "subscription_period",
              "subscription_period_end",
              "subscription_period_start",
              "subscription_start_date",
              "subscription_status",
              "subscription_true_false",
              "syncdate",
              "tax",
              "tax_fee",
              "tax_fee_in_usd",
              "tax_in_usd",
              "transaction_amount",
              "transaction_amount_in_usd",
              "transaction_currency",
              "transaction_date",
              "transaction_day",
              "transaction_fee",
              "transaction_fee_in_usd",
              "transaction_item_count",
              "transaction_month",
              "transaction_rate",
              "transaction_time_utc",
              "transaction_year"
            ]
          },
          "groupBy": {
            "type": "array",
            "description": "Fields used to group or aggregate the data:\n\n| Field                    | Description                                                                                                                                                                                                                                                                                                             |\n|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|\n| `Buyer_Email`            | Email used by the buyer for the transaction(s)                                                                                                                                                                                                                                                                                                                                                          |\n| `Buyer_ID`               | Unique ID allocated to buyer in FastSpring's internal system                                                                                                                                                                                                                                                                                                                                             |\n| `Chargeback_True_False`  | This is to identify if there was a chargeback on a given transaction. If the value is \"True\", there is a chargeback on the transaction and if the value is \"False\" there is no chargeback on the transaction.                                                                                                                                                                                                         |\n| `Company_ID`             | The company ID as used by businesses on FastSpring system. This is the ID used to access the app.fastspring.com and get to the landing page with all the stores(SITE_IDs) within a company.                                                                                                                                                                                                                     |\n| `Company_Name`           | Name of the business as registered with FastSpring                                                                                                                                                                                                                                                                                                                                                         |\n| `Country_ISO`            | 2 letter code that represent a country. This is used to capture the country in buyer's address                                                                                                                                                                                                                                                                                                               |\n| `Country_Name`           | Name of the country in buyer's address for a given transaction record                                                                                                                                                                                                                                                                                                                                       |\n| `Coupon`                 | \"Name of the coupon applied on a given transaction.<br>Example: As this field can be used to group the records, the reports with transaction grouped by COUPON along with INCOME can give insights into how much revenue each coupon is driving for the business.\"                                                                                                                                           |\n| `Discount`               | The discount amount.                                                                                                                                                                                                                                                                                                      |\n| `Driving_Offer_Type`     | The type of offer that is associated with the parent product in an order is captured in this field. The exhaustive list of offer types is as follows:<br>cross-sell<br>upsell<br>options<br>bundle<br>configuration<br>one_time_fee<br>addon                                                                                                                                                                    |\n| `Driving_Product_Path`   | The parent product with other products tagged in offers is captured here. The product path differs from the product name as the product path is used in the backend by FastSpring system to identify the products. But they both represent the same entity, the products in the catalog.                                                                                                                                      |\n| `End_Date`               | The end date of a transaction or subscription.                                                                                                                                                                                                                                                                             |\n| `Item_ID`                | Unique ID for item(s) in a given order. In a B2B context, there could be large volumes of products within an order and this can help in identifying an item in such large orders.                                                                                                                                                                                                                           |\n| `Order_ID`               | Unique ID for a given transaction. Order and transaction are used interchangeably here and any unique order or transaction can be identified using Order_ID.                                                                                                                                                                                                                                              |\n| `Product_Display_Name`   | The product name displayed to the customers on the checkout page.                                                                                                                                                                                                                                                                                                                                    |\n| `Product_ID`             | Unique ID for a product.                                                                                                                                                                                                                                                                                                                                                                      |\n| `Product_Name`           | The product name.                                                                                                                                                                                                                                                                                                                                                                            |\n| `Product_Path`           | The primary key for the product.                                                                                                                                                                                                                                                                                                                                                                |\n| `Purchase_Type`          | For subscriptions, this field can differentiate between first bill vs recurring bill, meaning when a customer pays for the first time, it is tagged \"First\" in this field. From the next payment for rest of the subscription's active lifetime, this will be \"Recurring\" as the customer is paying for the subscription service at fixed intervals (monthly, quarterly etc.).                                 |\n| `Return_True_False`      | This field can be used to know if a transaction has been returned or not. For transactions where the item(s) are returned, this field will have a \"True\" value and if the transaction did not have problems and went through successfully, this will have a \"False\".                                                                                                                                                          |\n| `Segment`                | If transactions pass value for reserved Key in Order tag (JSON Key value pair passed in session) FS_Segmentation (Not case sensitive), the data can be grouped.<br>Eg: Order tag with Key: Value in order tag with _FS_Segmentation: B2B can be used to group all the other fields for just B2B segment.                                                                                                                     |\n| `Start_Date`             | The start date of a transaction or subscription.                                                                                                                                                                                                                                                                          |\n| `Store_ID`               | A unique identifier associated with a store within a company. If a business has multiple stores for different purposes (For example, a store for B2C business vs another store for B2B business), this can be used to filter all the transactions for a specific store.                                                                                                                                          |\n| `Store_Name`             | In the hierarchy, a company can have multiple stores under it. This field stores the Store name. The usual purpose of this hierarchy is to separate the categories of business within a company. For example, a business may want to have a separate store for B2C business vs B2B business.                                                                                                                    |\n| `Subscription_Period`    | The subscription length.                                                                                                                                                                                                                                                                                                  |\n| `Subscription_Status`    | The subscription status.                                                                                                                                                                                                                                                                                                  |\n| `Subscription_True_False`| `true` if subscription product, `false` if not.                                                                                                                                                                                                                                                                           |\n| `Transaction_Currency`   | The currency of the order.                                                                                                                                                                                                                                                                                                 |\n| `Transaction_Date`       | The date of the order.                                                                                                                                                                                                                                                                                                     |\n| `Transaction_Day`        | Day-month of the subscription transaction.                                                                                                                                                                                                                                                                                                                                                        |\n| `Transaction_Month`      | Month of the transaction.                                                                                                                                                                                                                                                                                                 |\n| `Transaction_Year`       | Year of the transaction.                                                                                                                                                                                                                                                                                                  |\n",
            "items": {
              "type": "string"
            },
            "example": [
              "buyer_email",
              "buyer_id",
              "chargeback_true_false",
              "company_id",
              "company_name",
              "country_iso",
              "country_name",
              "coupon",
              "discount",
              "driving_offer_type",
              "driving_product_path",
              "end_date",
              "item_id",
              "order_id",
              "product_display_name",
              "product_id",
              "product_name",
              "product_path",
              "purchase_type",
              "return_true_false",
              "segment",
              "start_date",
              "store_id",
              "store_name",
              "subscription_period",
              "subscription_status",
              "subscription_true_false",
              "transaction_currency",
              "transaction_date",
              "transaction_day",
              "transaction_month",
              "transaction_year",
              "countryiso",
              "enddate",
              "productpath",
              "startdate",
              "syncdate"
            ]
          },
          "pageCount": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "maximum": 1000,
            "description": "Number of records to return per page.",
            "example": 30
          },
          "pageNumber": {
            "type": "integer",
            "format": "int32",
            "minimum": 1,
            "description": "Specifies the page number of results to be returned.",
            "example": 1
          },
          "async": {
            "type": "boolean",
            "description": "Indicates if the report generation was requested asynchronously.",
            "example": false
          },
          "notificationEmails": {
            "type": "array",
            "description": "List of emails to notify upon job completion.",
            "items": {
              "type": "string"
            },
            "example": "jane.doe@example.com"
          }
        }
      },
      "PostRevenueSyncResponse": {
        "title": "Revenue Sync Response",
        "type": "object",
        "properties": {
          "report": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "coupon": {
                  "type": "string",
                  "description": "Name of the coupon applied on a given transaction.",
                  "example": "10OFF"
                },
                "company_name": {
                  "type": "string",
                  "description": "Name of the business as registered with FastSpring.",
                  "example": "FastSpring QA"
                },
                "country_name": {
                  "type": "string",
                  "description": "The country name.",
                  "example": "Colombia"
                },
                "country_iso": {
                  "type": "string",
                  "description": "The country code in 2-letter ISO format.",
                  "example": "CO"
                },
                "return_true_false": {
                  "type": "boolean",
                  "description": "Indicates if a transaction has been returned. `true` if the transaction was returned, `false` otherwise.",
                  "example": false
                }
              }
            }
          },
          "request": {
            "type": "object",
            "properties": {
              "filter": {
                "type": "object",
                "properties": {
                  "startDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Start date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-01-01"
                  },
                  "endDate": {
                    "type": "string",
                    "format": "date",
                    "description": "End date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-12-31"
                  },
                  "syncDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Last update reference date (YYYY-MM-DD).",
                    "example": "2024-12-31"
                  },
                  "productNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product names to filter by.",
                    "example": [
                      "toxin product (toxin-product)"
                    ]
                  },
                  "productPaths": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product paths to filter the report by.",
                    "example": [
                      "toxin-product"
                    ]
                  },
                  "countryISO": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "The country code in 2-letter ISO format.",
                    "example": [
                      "CO"
                    ]
                  }
                }
              },
              "reportColumns": {
                "type": "array",
                "description": "Columns to include in the revenue report.",
                "items": {
                  "type": "string"
                },
                "example": [
                  "Company_Name",
                  "Country_ISO",
                  "Country_Name",
                  "Coupon"
                ]
              },
              "groupBy": {
                "type": "array",
                "description": "Fields used to group or aggregate the data.",
                "items": {
                  "type": "string"
                },
                "example": [
                  "Company_Name",
                  "Country_ISO",
                  "Country_Name"
                ]
              },
              "pageCount": {
                "type": "integer",
                "format": "int32",
                "minimum": 1,
                "maximum": 1000,
                "description": "Number of records to return per page.",
                "example": 30
              },
              "pageNumber": {
                "type": "integer",
                "format": "int32",
                "minimum": 1,
                "description": "Specifies the page number of results to be returned.",
                "example": 1
              },
              "async": {
                "type": "boolean",
                "description": "Indicates if the report generation was requested asynchronously.",
                "example": false
              },
              "notificationEmails": {
                "type": "array",
                "description": "List of emails to notify upon job completion.",
                "items": {
                  "type": "string"
                },
                "example": "jane.doe@example.com"
              }
            }
          }
        }
      },
      "PostRevenueAsyncResponse": {
        "title": "Revenue Async Response",
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the asynchronous job generating the revenue report.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "name": {
            "type": "string",
            "description": "Name assigned to the revenue report job.",
            "example": "RevenueReport"
          },
          "status": {
            "type": "string",
            "description": "Current status of the asynchronous report generation (e.g., PROCESSING, COMPLETED, FAILED).",
            "example": "PROCESSING"
          },
          "request": {
            "type": "object",
            "properties": {
              "filter": {
                "type": "object",
                "properties": {
                  "startDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Start date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-01-01"
                  },
                  "endDate": {
                    "type": "string",
                    "format": "date",
                    "description": "End date (YYYY-MM-DD) for the reporting range.",
                    "example": "2025-12-31"
                  },
                  "syncDate": {
                    "type": "string",
                    "format": "date",
                    "description": "Last update reference date (YYYY-MM-DD).",
                    "example": "2024-12-31"
                  },
                  "productNames": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product names to filter by.",
                    "example": [
                      "toxin product (toxin-product)"
                    ]
                  },
                  "productPaths": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "List of product paths to filter the report by.",
                    "example": [
                      "toxin-product"
                    ]
                  },
                  "countryISO": {
                    "type": "array",
                    "items": {
                      "type": "string"
                    },
                    "description": "The country code in 2-letter ISO format.",
                    "example": [
                      "CO"
                    ]
                  }
                }
              },
              "reportColumns": {
                "type": "array",
                "description": "Columns to include in the revenue report.",
                "items": {
                  "type": "string",
                  "example": [
                    "Company_Name",
                    "Country_ISO",
                    "Country_Name",
                    "Coupon"
                  ]
                }
              },
              "groupBy": {
                "type": "array",
                "description": "Fields used to group or aggregate the data.",
                "items": {
                  "type": "string",
                  "example": [
                    "Company_Name",
                    "Country_ISO",
                    "Country_Name"
                  ]
                }
              },
              "pageCount": {
                "type": "integer",
                "format": "int32",
                "minimum": 1,
                "maximum": 1000,
                "description": "Number of records to return per page.",
                "example": 30
              },
              "pageNumber": {
                "type": "integer",
                "format": "int32",
                "minimum": 1,
                "description": "Specifies the page number of results to be returned.",
                "example": 1
              },
              "async": {
                "type": "boolean",
                "description": "Indicates if the report generation was requested asynchronously.",
                "example": false
              },
              "notificationEmails": {
                "type": "array",
                "description": "List of emails to notify upon job completion.",
                "items": {
                  "type": "string",
                  "example": "jane.doe@example.com"
                }
              }
            }
          }
        }
      },
      "PostRevenueResponseBad": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "HTTP status code.",
            "example": "BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "Timestamp indicating when the error occurred.",
            "example": "01-01-2025 05:30:00"
          },
          "id": {
            "type": "string",
            "description": "A unique identifier for this particular error instance.",
            "example": "ABCDEFGH1IJKLMNOPQRSTUV23W"
          },
          "message": {
            "type": "string",
            "description": "An error message describing the issue.",
            "example": "Invalid column for grouping"
          },
          "details": {
            "type": "string",
            "nullable": true,
            "description": "Additional details or context about the error, if available.",
            "example": null
          },
          "errors": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A code representing the specific error condition.",
                  "example": "invalid.column"
                },
                "field": {
                  "type": "string",
                  "description": "The name of the field or property involved in the error, if applicable.",
                  "example": "item_id"
                },
                "message": {
                  "type": "string",
                  "description": "A description of this specific error.",
                  "example": "Invalid column for grouping"
                },
                "rejectedValue": {
                  "type": "string",
                  "nullable": true,
                  "description": "The value that was rejected or caused the error, if any.",
                  "example": "item_id"
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

List all jobs

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all jobs

Returns a list of all jobs and their status.


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/jobs": {
      "get": {
        "summary": "List all jobs",
        "tags": [
          "Data"
        ],
        "operationId": "getJobs",
        "description": "Returns a list of all jobs and their status.\n",
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetJobsResponse"
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
      "GetJobsResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the job.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "status": {
            "type": "string",
            "description": "Current status of the job (e.g., PENDING, PROCESSING, COMPLETE, FAILED).",
            "example": "COMPLETE"
          },
          "name": {
            "type": "string",
            "description": "Name assigned to the job.",
            "example": "RevenueReport"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the job was created.",
            "example": "2025-01-01T12:11:01"
          },
          "completeDate": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the job was completed.",
            "example": "2025-01-01T12:12:35"
          },
          "downloadUrl": {
            "type": "string",
            "format": "uri",
            "description": "URL to download the report once the job is complete.",
            "example": "https://api.fastspring.com/data/v1/downloads/JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "notificationEmails": {
            "type": "array",
            "description": "List of emails to notify upon job completion.",
            "items": {
              "type": "string",
              "format": "email"
            },
            "example": [
              "jane.doe@example.com"
            ]
          }
        },
        "example": [
          {
            "id": "JOBABCDEFGHIJKLMNOPQRS1TUVWX",
            "status": "COMPLETE",
            "name": "RevenueReport",
            "created": "2025-01-01T12:10:01",
            "completeDate": "2025-01-01T12:11:01",
            "downloadUrl": "https://api.fastspring.com/data/v1/downloads/JOBABCDEFGHIJKLMNOPQRS1TUVWX",
            "notificationEmails": [
              "jane.doe@example.com"
            ]
          },
          {
            "id": "JOBABCDEFGHIJKLMNOPQRS1TUVYZ",
            "status": "COMPLETE",
            "name": "SubscriptionReport",
            "created": "2025-01-01T12:12:01",
            "completeDate": "2025-01-01T12:13:01",
            "downloadUrl": "https://api.fastspring.com/data/v1/downloads/JOBABCDEFGHIJKLMNOPQRS1TUVYZ",
            "notificationEmails": [
              "jane.doe@example.com"
            ]
          }
        ]
      }
    }
  }
}
```

Retrieve a job

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Retrieve a job

Returns the details of an existing job with the given `job_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/jobs/{job_id}": {
      "get": {
        "summary": "Retrieve a job",
        "tags": [
          "Data"
        ],
        "operationId": "getJobById",
        "description": "Returns the details of an existing job with the given `job_id`.",
        "parameters": [
          {
            "name": "job_id",
            "in": "path",
            "required": true,
            "description": "A unique identifier for the job.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX",
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
                  "$ref": "#/components/schemas/GetJobIdResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetJobIdResponseBad"
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
      "GetJobIdResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier for the job.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "status": {
            "type": "string",
            "description": "Current status of the job (e.g., PENDING, PROCESSING, COMPLETE, FAILED).",
            "example": "COMPLETE"
          },
          "name": {
            "type": "string",
            "description": "Name assigned to the job.",
            "example": "RevenueReport"
          },
          "created": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the job was created.",
            "example": "2025-01-01T12:11:01"
          },
          "completeDate": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the job was completed.",
            "example": "2025-01-01T12:12:35"
          },
          "downloadUrl": {
            "type": "string",
            "format": "uri",
            "description": "URL to download the report once the job is complete.",
            "example": "https://api.fastspring.com/data/v1/downloads/JOBABCDEFGHIJKLMNOPQRS1TUVWX"
          },
          "notificationEmails": {
            "type": "array",
            "description": "List of emails to notify upon job completion.",
            "items": {
              "type": "string",
              "format": "email"
            },
            "example": [
              "jane.doe@example.com"
            ]
          }
        }
      },
      "GetJobIdResponseBad": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "HTTP status code.",
            "example": "BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "Timestamp indicating when the error occurred.",
            "example": "01-01-2025 05:30:00"
          },
          "id": {
            "type": "string",
            "description": "A unique identifier for this particular error instance.",
            "example": "ABCDEFGH1IJKLMNOPQRSTUV23W"
          },
          "message": {
            "type": "string",
            "description": "An error message describing the issue.",
            "example": "Invalid JobId"
          },
          "details": {
            "type": "string",
            "nullable": true,
            "description": "Additional details or context about the error, if available.",
            "example": null
          },
          "errors": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A code representing the specific error condition.",
                  "example": "invalid"
                },
                "field": {
                  "type": "string",
                  "description": "The name of the field or property involved in the error, if applicable.",
                  "example": "jobId"
                },
                "message": {
                  "type": "string",
                  "description": "A description of this specific error.",
                  "example": "Invalid JobId"
                },
                "rejectedValue": {
                  "type": "string",
                  "nullable": true,
                  "description": "The value that was rejected or caused the error, if asynchronously.",
                  "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
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

Reset cache

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Reset cache

Resets the cache for data service endpoints.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/util/cache": {
      "get": {
        "summary": "Reset cache",
        "tags": [
          "Data"
        ],
        "operationId": "resetCache",
        "description": "Resets the cache for data service endpoints.",
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "text/plain": {
                "schema": {
                  "type": "string",
                  "example": "Cache reset success"
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

Download a report

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Download a report

Downloads a report with the given `job_id`.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Data",
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
      "name": "Data",
      "description": "Generate and download sync or async reports and verify job status.\n"
    }
  ],
  "paths": {
    "/data/v1/downloads/{job_id}": {
      "get": {
        "summary": "Download a report",
        "tags": [
          "Data"
        ],
        "operationId": "downloadReport",
        "description": "Downloads a report with the given `job_id`.",
        "parameters": [
          {
            "name": "job_id",
            "in": "path",
            "required": true,
            "description": "A unique identifier for the job.",
            "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX",
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "text/plain": {
                "schema": {
                  "type": "string",
                  "example": "arr,subscriptions,subscriber_loss,mrr_add_on,product_name 0.0,1.0,0.0,0.0,primary subscription (addon-subscription)"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetDownloadIdResponseBad"
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
      "GetDownloadIdResponseBad": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "HTTP status code.",
            "example": "BAD_REQUEST"
          },
          "timestamp": {
            "type": "string",
            "description": "Timestamp indicating when the error occurred.",
            "example": "01-01-2025 05:30:00"
          },
          "id": {
            "type": "string",
            "description": "A unique identifier for this particular error instance.",
            "example": "ABCDEFGH1IJKLMNOPQRSTUV23W"
          },
          "message": {
            "type": "string",
            "description": "An error message describing the issue.",
            "example": "Invalid JobId"
          },
          "details": {
            "type": "string",
            "nullable": true,
            "description": "Additional details or context about the error, if available.",
            "example": null
          },
          "errors": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "errorCode": {
                  "type": "string",
                  "description": "A code representing the specific error condition.",
                  "example": "invalid"
                },
                "field": {
                  "type": "string",
                  "description": "The name of the field or property involved in the error, if applicable.",
                  "example": "jobId"
                },
                "message": {
                  "type": "string",
                  "description": "A description of this specific error.",
                  "example": "Invalid JobId"
                },
                "rejectedValue": {
                  "type": "string",
                  "nullable": true,
                  "description": "The value that was rejected or caused the error, if asynchronously.",
                  "example": "JOBABCDEFGHIJKLMNOPQRS1TUVWX"
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