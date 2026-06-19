List all processed events

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all processed events

Returns a list of processed events within a specific time range.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Events",
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
      "name": "Events",
      "description": "List and update the status of processed and unprocessed webhook events. If you have not subscribed to webhook events, you can retrieve the same information through other API endpoints.\n"
    }
  ],
  "paths": {
    "/events/processed": {
      "get": {
        "summary": "List all processed events",
        "tags": [
          "Events"
        ],
        "description": "Returns a list of processed events within a specific time range.",
        "operationId": "Getprocessedevents",
        "deprecated": false,
        "parameters": [
          {
            "name": "days",
            "in": "query",
            "required": true,
            "schema": {
              "type": "integer",
              "format": "int32",
              "maximum": 30
            },
            "description": "The number of days for which the data is requested, with a maximum of 30 days. The /events endpoint does not return events that are more than 30 days old.",
            "example": 30
          },
          {
            "name": "begin",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include events after the specified begin date (in YYYY-MM-DD format). Must be at least one day before the specified end date.",
            "example": "2025-01-01"
          },
          {
            "name": "end",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include events before the specified end date (in YYYY-MM-DD format).",
            "example": "2025-01-31"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetEventsResponse"
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
      "GetEventsResponse": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API.",
            "example": "events.get"
          },
          "result": {
            "type": "string",
            "description": "The result of the action (e.g., success or error).",
            "example": "success"
          },
          "page": {
            "type": "integer",
            "description": "Current page of results, if pagination is used.",
            "example": 1
          },
          "limit": {
            "type": "integer",
            "description": "Maximum number of results per page, if pagination is used.",
            "example": 50
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results returned.",
            "example": null
          },
          "total": {
            "type": "integer",
            "description": "Total number of events returned in the response.",
            "example": 1
          },
          "events": {
            "type": "array",
            "description": "List of events returned by the API.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Identifier for the event in data array.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                },
                "processed": {
                  "type": "boolean",
                  "description": "Indicates if this event has been processed.",
                  "example": true
                },
                "created": {
                  "type": "integer",
                  "description": "Creation timestamp of the event.",
                  "example": 1729545999690
                },
                "type": {
                  "type": "string",
                  "description": "Type of event.",
                  "example": "account.updated"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the event occurred in a live environment.",
                  "example": true
                },
                "data": {
                  "type": "object",
                  "description": "Detailed data associated with the event.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Identifier associated with the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier in the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "contact": {
                      "type": "object",
                      "description": "Contact details in the event.",
                      "properties": {
                        "first": {
                          "type": "string",
                          "description": "First name of the contact.",
                          "example": "First"
                        },
                        "last": {
                          "type": "string",
                          "description": "Last name of the contact.",
                          "example": "Last"
                        },
                        "email": {
                          "type": "string",
                          "description": "Email address of the contact.",
                          "example": "first.last@domain.com"
                        },
                        "company": {
                          "type": "string",
                          "description": "Company name associated with the contact.",
                          "example": "Company Name"
                        },
                        "phone": {
                          "type": "string",
                          "description": "Contact's phone number.",
                          "example": "555-555-5555"
                        },
                        "subscribed": {
                          "type": "boolean",
                          "description": "Contact subscription status.",
                          "example": true
                        }
                      }
                    },
                    "address": {
                      "type": "object",
                      "description": "Address details associated with the account.",
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
                        "country": {
                          "type": "string",
                          "description": "Two-letter country code.",
                          "example": "US"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "ZIP or postal code.",
                          "example": 93101
                        },
                        "region": {
                          "type": "string",
                          "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                          "example": "US-CA"
                        },
                        "region custom": {
                          "type": "string",
                          "description": "Custom region if not in predefined regions.",
                          "example": null
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
                          "description": "Lookup identifiers for the event.",
                          "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
                        }
                      }
                    },
                    "url": {
                      "type": "string",
                      "description": "FastSpring account URL.",
                      "example": "https://user.test.onfastspring.com/account"
                    }
                  }
                },
                "event": {
                  "type": "string",
                  "description": "Unique identifier for the specific event.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                }
              }
            }
          },
          "data": {
            "type": "array",
            "description": "Detailed data related to each event, repeating the structure of the 'events' object.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Identifier for the event in data array.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                },
                "processed": {
                  "type": "boolean",
                  "description": "Indicates if this event has been processed.",
                  "example": true
                },
                "created": {
                  "type": "integer",
                  "description": "Creation timestamp of the event.",
                  "example": 1729545999690
                },
                "type": {
                  "type": "string",
                  "description": "Type of event.",
                  "example": "account.updated"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the event occurred in a live environment.",
                  "example": true
                },
                "data": {
                  "type": "object",
                  "description": "Detailed data associated with the event.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Identifier associated with the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier for the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "contact": {
                      "type": "object",
                      "description": "Contact details for the event.",
                      "properties": {
                        "first": {
                          "type": "string",
                          "description": "First name of the contact.",
                          "example": "First"
                        },
                        "last": {
                          "type": "string",
                          "description": "Last name of the contact.",
                          "example": "Last"
                        },
                        "email": {
                          "type": "string",
                          "description": "Email address of the contact.",
                          "example": "first.last@domain.com"
                        },
                        "company": {
                          "type": "string",
                          "description": "Company name associated with the contact.",
                          "example": "Company Name"
                        },
                        "phone": {
                          "type": "string",
                          "description": "Contact phone number.",
                          "example": "555-555-5555"
                        },
                        "subscribed": {
                          "type": "boolean",
                          "description": "Contact subscription status.",
                          "example": true
                        }
                      }
                    },
                    "address": {
                      "type": "object",
                      "description": "Address details associated with the account.",
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
                        "country": {
                          "type": "string",
                          "description": "Two-letter country code.",
                          "example": "US"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "ZIP or postal code.",
                          "example": 93101
                        },
                        "region": {
                          "type": "string",
                          "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                          "example": "US-CA"
                        },
                        "region custom": {
                          "type": "string",
                          "description": "Custom region if not in predefined regions.",
                          "example": null
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
                          "description": "Lookup identifiers for the event.",
                          "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
                        }
                      }
                    },
                    "url": {
                      "type": "string",
                      "description": "FastSpring account URL.",
                      "example": "https://user.test.onfastspring.com/account"
                    }
                  }
                }
              }
            }
          },
          "more": {
            "type": "boolean",
            "description": "Indicates if there are more results available. The response will return up to 25 unique events. If there are more than 25 results, the return contains a \"more\" attribute.",
            "example": false
          }
        }
      }
    }
  }
}
```

List all unprocessed events

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# List all unprocessed events

Returns a list of unprocessed events within a specific time range.

After you retrieve a missed event, you can mark it as processed by posting to the `/events` endpoint.  


# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Events",
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
      "name": "Events",
      "description": "List and update the status of processed and unprocessed webhook events. If you have not subscribed to webhook events, you can retrieve the same information through other API endpoints.\n"
    }
  ],
  "paths": {
    "/events/unprocessed": {
      "get": {
        "summary": "List all unprocessed events",
        "tags": [
          "Events"
        ],
        "description": "Returns a list of unprocessed events within a specific time range.\n\nAfter you retrieve a missed event, you can mark it as processed by posting to the `/events` endpoint.  \n",
        "operationId": "Getunprocessedevents",
        "deprecated": false,
        "parameters": [
          {
            "name": "days",
            "in": "query",
            "required": true,
            "schema": {
              "type": "integer",
              "format": "int32",
              "maximum": 30
            },
            "description": "The number of days for which the data is requested, with a maximum of 30 days. The /events endpoint does not return events that are more than 30 days old.",
            "example": 30
          },
          {
            "name": "begin",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include events after the specified begin date (in YYYY-MM-DD format). Must be at least one day before the specified end date.",
            "example": "2025-01-01"
          },
          {
            "name": "end",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Filters results to include events before the specified end date (in YYYY-MM-DD format).",
            "example": "2025-01-31"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetEventsResponseUnprocessed"
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
      "GetEventsResponseUnprocessed": {
        "type": "object",
        "properties": {
          "action": {
            "type": "string",
            "description": "The action performed by the API.",
            "example": "events.get"
          },
          "result": {
            "type": "string",
            "description": "The result of the action (e.g., success or error).",
            "example": "success"
          },
          "page": {
            "type": "integer",
            "description": "Current page of results, if pagination is used.",
            "example": 1
          },
          "limit": {
            "type": "integer",
            "description": "Maximum number of results per page, if pagination is used.",
            "example": 50
          },
          "nextPage": {
            "type": "integer",
            "description": "The next page number of results returned.",
            "example": null
          },
          "total": {
            "type": "integer",
            "description": "Total number of events returned in the response.",
            "example": 1
          },
          "events": {
            "type": "array",
            "description": "List of events returned by the API.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Identifier for the event in data array.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                },
                "processed": {
                  "type": "boolean",
                  "description": "Indicates if this event has been processed.",
                  "example": false
                },
                "created": {
                  "type": "integer",
                  "description": "Creation timestamp of the event.",
                  "example": 1729545999690
                },
                "type": {
                  "type": "string",
                  "description": "Type of event.",
                  "example": "account.updated"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the event occurred in a live environment.",
                  "example": true
                },
                "data": {
                  "type": "object",
                  "description": "Detailed data associated with the event.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Identifier associated with the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier for the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "contact": {
                      "type": "object",
                      "description": "Contact details for the event.",
                      "properties": {
                        "first": {
                          "type": "string",
                          "description": "First name of the contact.",
                          "example": "First"
                        },
                        "last": {
                          "type": "string",
                          "description": "Last name of the contact.",
                          "example": "Last"
                        },
                        "email": {
                          "type": "string",
                          "description": "Email address of the contact.",
                          "example": "first.last@domain.com"
                        },
                        "company": {
                          "type": "string",
                          "description": "Company name associated with the contact.",
                          "example": "Company Name"
                        },
                        "phone": {
                          "type": "string",
                          "description": "Contact phone number.",
                          "example": "555-555-5555"
                        },
                        "subscribed": {
                          "type": "boolean",
                          "description": "Contact subscription status.",
                          "example": true
                        }
                      }
                    },
                    "address": {
                      "type": "object",
                      "description": "Address details associated with the account.",
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
                        "country": {
                          "type": "string",
                          "description": "Two-letter country code.",
                          "example": "US"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "ZIP or postal code.",
                          "example": 93101
                        },
                        "region": {
                          "type": "string",
                          "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                          "example": "US-CA"
                        },
                        "region custom": {
                          "type": "string",
                          "description": "Custom region if not in predefined regions.",
                          "example": null
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
                          "description": "Lookup identifiers for the event.",
                          "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
                        }
                      }
                    },
                    "url": {
                      "type": "string",
                      "description": "FastSpring account URL.",
                      "example": "https://user.test.onfastspring.com/account"
                    }
                  }
                },
                "event": {
                  "type": "string",
                  "description": "Unique identifier for the specific event.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                }
              }
            }
          },
          "data": {
            "type": "array",
            "description": "Detailed data related to each event, repeating the structure of the 'events' object.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "Identifier for the event in data array.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                },
                "processed": {
                  "type": "boolean",
                  "description": "Indicates if this event has been processed.",
                  "example": true
                },
                "created": {
                  "type": "integer",
                  "description": "Creation timestamp of the event.",
                  "example": 1729545999690
                },
                "type": {
                  "type": "string",
                  "description": "Type of event.",
                  "example": "account.updated"
                },
                "live": {
                  "type": "boolean",
                  "description": "Indicates if the event occurred in a live environment.",
                  "example": true
                },
                "data": {
                  "type": "object",
                  "description": "Detailed data associated with the event.",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Identifier associated with the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "account": {
                      "type": "string",
                      "description": "Account identifier for the event.",
                      "example": "abcDEFgHiJklM1N-3OP9qRST"
                    },
                    "contact": {
                      "type": "object",
                      "description": "Contact details in the event.",
                      "properties": {
                        "first": {
                          "type": "string",
                          "description": "First name of the contact.",
                          "example": "First"
                        },
                        "last": {
                          "type": "string",
                          "description": "Last name of the contact.",
                          "example": "Last"
                        },
                        "email": {
                          "type": "string",
                          "description": "Email address of the contact.",
                          "example": "first.last@domain.com"
                        },
                        "company": {
                          "type": "string",
                          "description": "Company name associated with the contact.",
                          "example": "Company Name"
                        },
                        "phone": {
                          "type": "string",
                          "description": "Contact phone number.",
                          "example": "555-555-5555"
                        },
                        "subscribed": {
                          "type": "boolean",
                          "description": "Contact subscription status.",
                          "example": true
                        }
                      }
                    },
                    "address": {
                      "type": "object",
                      "description": "Address details associated with the account.",
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
                        "country": {
                          "type": "string",
                          "description": "Two-letter country code.",
                          "example": "US"
                        },
                        "postalCode": {
                          "type": "string",
                          "description": "ZIP or postal code.",
                          "example": 93101
                        },
                        "region": {
                          "type": "string",
                          "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                          "example": "US-CA"
                        },
                        "region custom": {
                          "type": "string",
                          "description": "Custom region if not in predefined regions.",
                          "example": null
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
                          "description": "Lookup identifiers for the event.",
                          "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
                        }
                      }
                    },
                    "url": {
                      "type": "string",
                      "description": "FastSpring account URL.",
                      "example": "https://user.test.onfastspring.com/account"
                    }
                  }
                }
              }
            }
          },
          "more": {
            "type": "boolean",
            "description": "Indicates if there are more results available. The response will return up to 25 unique events. If there are more than 25 results, the return contains a \"more\" attribute.",
            "example": false
          }
        }
      }
    }
  }
}
```

Update an event

> ## Documentation Index
> Fetch the complete documentation index at: https://developer.fastspring.com/llms.txt
> Use this file to discover all available pages before exploring further.

# Update an event

Marks an event with the given `event_id` as either **processed** or **unprocessed**.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "description": "Sample",
    "version": "1.0",
    "title": "FastSpring API - Events",
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
      "name": "Events",
      "description": "List and update the status of processed and unprocessed webhook events. If you have not subscribed to webhook events, you can retrieve the same information through other API endpoints.\n"
    }
  ],
  "paths": {
    "/events/{event_id}": {
      "post": {
        "summary": "Update an event",
        "tags": [
          "Events"
        ],
        "description": "Marks an event with the given `event_id` as either **processed** or **unprocessed**.",
        "operationId": "Updateasingleevent",
        "deprecated": false,
        "parameters": [
          {
            "name": "event_id",
            "in": "path",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "The unique identifier for the event.",
            "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateasingleeventRequest"
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
                  "$ref": "#/components/schemas/EventsUpdateResponse"
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/EventsUpdateError400NotFound"
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
      "UpdateasingleeventRequest": {
        "type": "object",
        "properties": {
          "processed": {
            "type": "boolean",
            "description": "Indicates whether the event has been processed.",
            "example": true
          }
        },
        "required": [
          "processed"
        ]
      },
      "EventsUpdateResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Identifier for the event in data array.",
            "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
          },
          "processed": {
            "type": "boolean",
            "description": "Indicates if this event has been processed.",
            "example": true
          },
          "created": {
            "type": "integer",
            "description": "Creation timestamp of the event.",
            "example": 1729545999690
          },
          "type": {
            "type": "string",
            "description": "Type of event.",
            "example": "account.updated"
          },
          "live": {
            "type": "boolean",
            "description": "Indicates if the event occurred in a live environment.",
            "example": true
          },
          "data": {
            "type": "object",
            "description": "Detailed data associated with the event.",
            "properties": {
              "id": {
                "type": "string",
                "description": "Identifier associated with the event.",
                "example": "abcDEFgHiJklM1N-3OP9qRST"
              },
              "account": {
                "type": "string",
                "description": "Account identifier in the event.",
                "example": "abcDEFgHiJklM1N-3OP9qRST"
              },
              "contact": {
                "type": "object",
                "description": "Contact details in the event.",
                "properties": {
                  "first": {
                    "type": "string",
                    "description": "First name of the contact.",
                    "example": "First"
                  },
                  "last": {
                    "type": "string",
                    "description": "Last name of the contact.",
                    "example": "Last"
                  },
                  "email": {
                    "type": "string",
                    "description": "Email address of the contact.",
                    "example": "first.last@domain.com"
                  },
                  "company": {
                    "type": "string",
                    "description": "Company name associated with the contact.",
                    "example": "Company Name"
                  },
                  "phone": {
                    "type": "string",
                    "description": "Contact's phone number.",
                    "example": "555-555-5555"
                  },
                  "subscribed": {
                    "type": "boolean",
                    "description": "Contact subscription status.",
                    "example": true
                  }
                }
              },
              "address": {
                "type": "object",
                "description": "Address details associated with the account.",
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
                  "country": {
                    "type": "string",
                    "description": "Two-letter country code.",
                    "example": "US"
                  },
                  "postalCode": {
                    "type": "string",
                    "description": "ZIP or postal code.",
                    "example": 93101
                  },
                  "region": {
                    "type": "string",
                    "description": "Region code (<a href=\"https://en.wikipedia.org/wiki/ISO_3166-2\">ISO 3166-2</a>).",
                    "example": "US-CA"
                  },
                  "region custom": {
                    "type": "string",
                    "description": "Custom region if not in predefined regions.",
                    "example": null
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
                    "description": "Lookup identifiers for the event.",
                    "example": "ab-Cd9e8F6ghiJ-Okl3M3n"
                  }
                }
              },
              "url": {
                "type": "string",
                "description": "FastSpring account URL.",
                "example": "https://user.test.onfastspring.com/account"
              }
            }
          }
        }
      },
      "EventsUpdateError400NotFound": {
        "type": "object",
        "properties": {
          "events": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "action": {
                  "type": "string",
                  "description": "The action performed by the API call.",
                  "example": "event.get"
                },
                "event": {
                  "type": "string",
                  "description": "The unique order identifier.",
                  "example": "ABCDEFGHIJK1LMNOPQR2STUV3WXYZAB"
                },
                "result": {
                  "type": "string",
                  "description": "The result of the action (e.g., success or error).",
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
      }
    }
  }
}
```