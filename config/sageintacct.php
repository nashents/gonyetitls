<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sage Intacct Integration — non-secret defaults
    |--------------------------------------------------------------------------
    |
    | IMPORTANT: Credentials (company_id, user_id, user_password, sender_id,
    | sender_password and any REST client_id / client_secret) are NEVER stored
    | here. They live encrypted in `company_integrations.credentials`, managed
    | per-company via the Integrations settings screen.
    |
    | This file only holds non-secret, environment-level defaults. Anything set
    | on a company's integration `config` JSON overrides the values here.
    */

    // Active driver when a company integration does not specify one.
    // 'xml' = Sage Intacct XML Web Services (legacy, proven, default).
    // 'rest' = Sage Intacct REST API (OAuth 2.0) — enable after onboarding.
    'default_driver' => env('SAGE_INTACCT_DRIVER', 'xml'),

    // Last-resort fallback currency. Every deployment MUST set the company base
    // currency (mandatory at instance setup) to match its Sage base — which is
    // NOT always ZAR; it varies per instance. The company's own currency is
    // always preferred, so this default should never actually be reached.
    'base_currency' => env('SAGE_INTACCT_BASE_CURRENCY', 'ZAR'),

    // XML Web Services gateway endpoint (same URL for sandbox and production;
    // the target company is selected by the credentials in the request body).
    'xml' => [
        'endpoint'      => env('SAGE_INTACCT_XML_URL', 'https://api.intacct.com/ia/xml/xmlgw.phtml'),
        // Control id / unique request id prefix sent on every XML request.
        'control_id'    => env('SAGE_INTACCT_CONTROL_ID', 'gonyeti'),
        // The web services partner application id. Usually the same as sender_id.
        'dtd_version'   => '3.0',
    ],

    // REST API base URLs per environment. Only used by the REST driver.
    'rest' => [
        'base_url'  => env('SAGE_INTACCT_REST_URL', 'https://api.intacct.com/ia/api/v1'),
        'token_url' => env('SAGE_INTACCT_REST_TOKEN_URL', 'https://api.intacct.com/ia/api/v1/oauth2/token'),
    ],

    // HTTP timeout (seconds) for all Sage requests.
    'timeout' => env('SAGE_INTACCT_TIMEOUT', 30),

    /*
    | Phase 2 — Fleet (Classes) & Trips (Projects).
    | Per-company overrides may be set on the integration `config` JSON
    | (e.g. {"project_category": "Haulage"}); those win over these defaults.
    */

    // Sage CLASS constraints / conventions for Transporter/Horse/Trailer.
    'class' => [
        // CLASSID max length (Sage caps class ids); refs are trimmed to this.
        'id_max_length' => 20,
    ],

    // Sage PROJECT defaults. Transporters, Horses and Trips are all Projects.
    // Per-company overrides may be set on the integration `config` JSON, e.g.
    // {"project_category":"...","project_location_id":"...","project_department_id":"..."}.
    'project' => [
        // Required by Sage. Confirmed live in bhsquared-imp.
        'category'      => env('SAGE_INTACCT_PROJECT_CATEGORY', 'Contract'),

        // Confirmed live: E100 = "Hono Transport Logistics CC", D2-1 = "Sub Contracting".
        'location_id'   => env('SAGE_INTACCT_PROJECT_LOCATION_ID', 'E100'),
        'department_id' => env('SAGE_INTACCT_PROJECT_DEPARTMENT_ID', 'D2-1'),

        // PROJECTTYPE per entity (exact Sage type names).
        'types' => [
            'transporter' => env('SAGE_INTACCT_TYPE_TRANSPORTER', 'SUBCONTRACTOR'),
            'horse'       => env('SAGE_INTACCT_TYPE_HORSE', 'SUB - TRUCKS'),
            'trip'        => env('SAGE_INTACCT_TYPE_TRIP', 'TRIPS'),
        ],

        // Sage PROJECTSTATUS (the project workflow status). A trip is only synced
        // once offloaded/completed, so it lands "Completed"; the in-progress value
        // is kept for completeness. Must match the instance's project statuses
        // (In Progress / Completed / AVAILABLE in bhsquared-imp).
        'status_completed'   => env('SAGE_INTACCT_PROJECT_STATUS_COMPLETED', 'Completed'),
        'status_in_progress' => env('SAGE_INTACCT_PROJECT_STATUS_IN_PROGRESS', 'In Progress'),
    ],

    // Trip → PROJECT sync gate. Only trips whose authorization is "approved" AND
    // whose trip_status is one of these (offloaded/completed) are synced to Sage.
    'trip' => [
        'syncable_statuses' => ['Offloaded'],
    ],

    // Phase 3 — Trip expenses → Purchase Requisitions.
    'purchasing' => [
        // Exact Sage transaction definition name (confirmed live in bhsquared-imp).
        'requisition_type' => env('SAGE_INTACCT_REQUISITION_TYPE', 'Purchase requisition'),
        // Line unit of measure — existing requisition lines use "Each".
        'line_unit'        => env('SAGE_INTACCT_LINE_UNIT', 'Each'),
        // Operating entity the requisition must be created IN. bhsquared-imp is a
        // multi-entity company; every real requisition lives in entity E100 (Hono
        // Transport Logistics CC) — 152/155. Requisitions created at the shared
        // top level (empty MEGAENTITY) don't get the UI's Convert action, so we
        // scope the create session to this entity via the login <locationid>.
        // Null ⇒ create at top level (single-entity companies).
        'entity_id'        => env('SAGE_INTACCT_REQUISITION_ENTITY_ID', 'E100'),
        // Exchange-rate type Sage uses to look up the rate when a requisition
        // carries a transaction currency under an entity scope (required by the
        // schema; rate = 1 when currency == entity base). Sage's standard daily
        // rate table. Null ⇒ omit (only safe when no currency is ever sent).
        'exchange_rate_type' => env('SAGE_INTACCT_EXCHANGE_RATE_TYPE', 'Intacct Daily Rate'),

        // Dispatch Sheet: trip expenses/allowances paid via the drivers' paycard
        // vendor go onto a "Dispatch Sheet" (a Quote-class PO definition — same
        // create_potransaction mechanics as a requisition) instead of a Purchase
        // Requisition. Its not-editable / not-deletable / convertible workflow is
        // configured on the Sage definition itself; we only create the doc as this
        // type in the entity so the Convert action is available. Everything else
        // stays a Purchase Requisition.
        'dispatch_sheet_type'     => env('SAGE_INTACCT_DISPATCH_SHEET_TYPE', 'Dispatch Sheet'),
        // The paycard vendor (exact Sage VENDORID + name in bhsquared-imp). The
        // dispatch sheet always posts to this Sage vendor.
        'dispatch_vendor_sage_id' => env('SAGE_INTACCT_DISPATCH_VENDOR_SAGE_ID', 'SUP60102'),
        'dispatch_vendor_name'    => env('SAGE_INTACCT_DISPATCH_VENDOR_NAME', 'Paycard Main - Drivers Dispatch'),
        // The Dispatch Sheet definition REQUIRES two custom fields (integration
        // names below). Both are validated PICK-LISTS in Sage, so the values sent
        // (the trip's truck registration + driver name) must exist in those lists.
        'dispatch_reg_field'      => env('SAGE_INTACCT_DISPATCH_REG_FIELD', 'REG'),
        'dispatch_driver_field'   => env('SAGE_INTACCT_DISPATCH_DRIVER_FIELD', 'Driver'),

        // Fuel orders → "PR - Diesel" (a Quote-class PO definition; same shape as
        // the Dispatch Sheet — needs the REG + Driver custom fields, entity-scoped,
        // convertible). The supplier is the fuelling station.
        'diesel_type'             => env('SAGE_INTACCT_DIESEL_TYPE', 'PR - Diesel'),

        // Purchases → "Purchase order"; goods received → "Receipt" (Shipping
        // Receipt), created by converting the PO — each receipt line references
        // its PO line via <sourcelinekey>. Both entity-scoped. NOTE: the Receipt
        // definition affects GL, so received ITEMs must have AP GL accounts set.
        'purchase_order_type'     => env('SAGE_INTACCT_PURCHASE_ORDER_TYPE', 'Purchase order'),
        'receipt_type'            => env('SAGE_INTACCT_RECEIPT_TYPE', 'Receipt'),
    ],

    // Fuel (Gonyeti Fuel order → Sage PR - Diesel).
    'fuel' => [
        // Fuel orders post to the "PO - Diesel" definition (Order class). NOTE:
        // as of 2026-08-07 both PR- and PO - Diesel STILL enforce the required,
        // validated REG picklist (Sage has not yet switched them to CLASS +
        // EMPLOYEE), so the truck reg is rejected → requires_attention until they
        // do. Flip to 'PR - Diesel' + drop the REG/Driver custom fields the moment
        // that change lands (verified via API, not just the UI's "required" flag).
        'type'      => env('SAGE_INTACCT_FUEL_TYPE', 'PO - Diesel'),
        // Explicit diesel ITEMID to use on the line; when null, an item named
        // `item_name` is ensured (linked by name, else created).
        'item_id'   => env('SAGE_INTACCT_FUEL_ITEM_ID', null),
        'item_name' => env('SAGE_INTACCT_FUEL_ITEM_NAME', 'Diesel'),
        // Line unit of measure for the diesel line.
        'line_unit' => env('SAGE_INTACCT_FUEL_LINE_UNIT', 'Each'),
        // VENDORID prefix for a fuelling-station vendor auto-created from a
        // container when no Sage vendor of that name already exists.
        'station_vendor_prefix' => env('SAGE_INTACCT_STATION_VENDOR_PREFIX', 'FSTN-'),
    ],

    // Job cards (Gonyeti workshop Ticket → Sage Order-Entry job card).
    // The booking's transaction_type decides which Sage definition is used:
    //   expense → Internal Job Card ; income → the standard Job Card (invoice).
    'jobcard' => [
        // Booking transaction_type → definition: expense = "Internal Job Card",
        // income = the standard "Job-Card" (both are Order-class definitions;
        // "Job Card Invoice" is a separate Invoice-class billing doc, NOT this).
        'internal_type'     => env('SAGE_INTACCT_INTERNAL_JOBCARD_TYPE', 'Internal Job Card'),
        'standard_type'     => env('SAGE_INTACCT_JOBCARD_TYPE', 'Job-Card'),
        // The standard "Job-Card" definition requires a mileage custom field
        // (integration name "Milage_", free-text) — set from the ticket odometer.
        'mileage_field'     => env('SAGE_INTACCT_JOBCARD_MILEAGE_FIELD', 'Milage_'),
        // Close-off (both types) + reversal (internal only) definitions. Both are
        // conversions of the source job card (its lines referenced by sourcelinekey).
        'closeoff_type'         => env('SAGE_INTACCT_JOBCARD_CLOSEOFF_TYPE', 'Job-Card-Close Off'),
        'reversal_internal_type' => env('SAGE_INTACCT_JOBCARD_REVERSAL_TYPE', 'Reversal_Internal Job Card'),
        // Default Sage CUSTOMERID per type, used when the fleet's transporter
        // can't be matched to a Sage customer by name.
        'internal_customer' => env('SAGE_INTACCT_INTERNAL_JOBCARD_CUSTOMER', 'Sub-00007'),
        'standard_customer' => env('SAGE_INTACCT_JOBCARD_CUSTOMER', 'Sub-00004'),
        // Line item for labour when a job card has no part lines (ensured by name).
        'labour_item_name'  => env('SAGE_INTACCT_JOBCARD_LABOUR_ITEM', 'Workshop Labour'),
        'line_unit'         => env('SAGE_INTACCT_JOBCARD_LINE_UNIT', 'Each'),
    ],

    // Invoices (Gonyeti Invoice → Sage Order-Entry sales invoice). On approval:
    //   invoice to a Customer    → "OE sales invoice"
    //   invoice to a Transporter → "Job Card Invoice" (customer = the transporter)
    'invoice' => [
        'oe_type'                => env('SAGE_INTACCT_OE_INVOICE_TYPE', 'OE sales invoice'),
        'jobcard_type'           => env('SAGE_INTACCT_INVOICE_JOBCARD_TYPE', 'Job Card Invoice'),
        'line_unit'              => env('SAGE_INTACCT_INVOICE_LINE_UNIT', 'Each'),
        // CUSTOMERID prefix for a customer auto-created from a transporter when no
        // Sage customer of that name already exists (transporter = customer here).
        'transporter_customer_prefix' => env('SAGE_INTACCT_TRANSPORTER_CUSTOMER_PREFIX', 'TCUS-'),
        // Fallback line item (ensured by name) for invoice lines with no product
        // (e.g. custom / transport-order freight lines).
        'default_item_name'      => env('SAGE_INTACCT_INVOICE_DEFAULT_ITEM', 'Transportation'),
    ],

    // Stores ↔ Sage Warehouses. A store pushes to a WAREHOUSE (id WH-{store_id},
    // linking to an existing warehouse of the same NAME first). A multi-entity
    // company requires a LOCATIONID on each warehouse; when this is null the
    // service falls back to the operating entity (purchasing.entity_id, e.g.
    // E100). Set this only to override that with a specific LOCATION dimension.
    'warehouse' => [
        'location_id' => env('SAGE_INTACCT_WAREHOUSE_LOCATION_ID', null),
    ],

    // Sage ITEM defaults (Gonyeti Expense → Item).
    'item' => [
        'type'      => env('SAGE_INTACCT_ITEM_TYPE', 'Non-Inventory'),
        'id_prefix' => env('SAGE_INTACCT_ITEM_ID_PREFIX', 'EXP-'),
        // ITEMID prefix for items created from trip Allowances.
        'allowance_id_prefix' => env('SAGE_INTACCT_ALLOWANCE_ID_PREFIX', 'ALW-'),
        // ITEMID prefix for products pushed from the Gonyeti products module
        // (falls back to the Gonyeti product_number when it has one).
        'product_id_prefix' => env('SAGE_INTACCT_PRODUCT_ID_PREFIX', 'PRD-'),

        // Item GL Group (Sage ITEM.GLGROUP) — this is how GL posting accounts
        // (revenue / COGS / inventory) attach to an item. On push we prefer the
        // product's own `gl_group` (captured when it was pulled from Sage); these
        // are the fallbacks for products created fresh in Gonyeti. Must be a GL
        // group that EXISTS in the Sage instance. Inventory items need an
        // inventory-capable group ("Inventory" exists in bhsquared-imp). Null ⇒
        // omit GLGROUP (fine for Non-Inventory; Inventory creates may then fail).
        'gl_group'           => env('SAGE_INTACCT_ITEM_GL_GROUP', null),
        'gl_group_inventory' => env('SAGE_INTACCT_ITEM_GL_GROUP_INVENTORY', 'Inventory'),
        'taxable'   => env('SAGE_INTACCT_ITEM_TAXABLE', true),
        // Item tax group name so new expense items resolve a purchase tax
        // schedule. Confirmed settable via the API (nested <TAXGROUP><NAME>).
        // "Exempt" makes the requisition line resolve to "No Input VAT" (no VAT
        // calculated) — matching the client's existing trip-expense requisitions
        // (e.g. item N0667 "Admin Fee" is Exempt). "Standard Rate" instead adds
        // 15% input VAT, which these subcontractor/trip expenses must NOT have.
        // Set to null to skip.
        'tax_group' => env('SAGE_INTACCT_ITEM_TAX_GROUP', 'Exempt'),
    ],

    // Sage EMPLOYEE defaults (Gonyeti Driver's Employee → Employee).
    'employee' => [
        'id_prefix' => env('SAGE_INTACCT_EMPLOYEE_ID_PREFIX', 'EMP-'),
    ],

    // Sage VENDOR defaults. The vendor's pay-to/display CONTACT needs a tax
    // group so purchase requisitions resolve a tax schedule (item tax group ×
    // vendor tax group). NOTE: contact tax group is set with the FLAT form
    // (<TAXGROUP>value</TAXGROUP>), unlike ITEM which uses nested <TAXGROUP><NAME>.
    // The client's local vendors use "Local Suppliers". Null ⇒ omit.
    'vendor' => [
        'tax_group' => env('SAGE_INTACCT_VENDOR_TAX_GROUP', 'Local Suppliers'),
    ],

    // Sage CUSTOMER defaults. Like vendors, the customer's display CONTACT needs a
    // tax group (FLAT <TAXGROUP>) so sales invoices resolve a tax schedule
    // (item tax group × customer tax group). "Local Customers" is the sales-side
    // contact tax group in this instance. Null ⇒ omit.
    'customer' => [
        'tax_group' => env('SAGE_INTACCT_CUSTOMER_TAX_GROUP', 'Local Customers'),
    ],

    // Phase 4 — Pull from Sage → Gonyeti (reverse import).
    'pull' => [
        // Sage CLASSID prefixes that identify horses vs trailers (NAME=registration).
        'horse_class_prefixes'     => ['H', 'FHH'],
        'trailer_class_prefixes'   => ['T', 'FHT'],
        // Sage PROJECTTYPE that represents a transporter.
        'transporter_project_type' => env('SAGE_INTACCT_TYPE_TRANSPORTER', 'SUBCONTRACTOR'),
        // Page size for readByQuery (max 1000). readMore() pages beyond this.
        'page_size'                => 1000,
    ],

];
