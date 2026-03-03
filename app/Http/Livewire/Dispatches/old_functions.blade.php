  public function inventoryFIFO($dispatch)
    {
      
        $dispatch_total = 0;

        // 1) Resolve collection based on department
        if ($this->department === 'tyre') {
            $collection = $this->selectedTyre ?? [];
        } elseif ($this->department === 'asset') {
            $collection = $this->selectedAsset ?? [];
        } elseif ($this->department === 'inventory') {
            $collection = $this->selectedInventory ?? [];
        } else {
            return 0; // unknown department
        }

         

        if (empty($collection)) {
            return 0;
        }

        foreach ($collection as $key => $collectionId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);
            if ($requestedQty <= 0) {
                continue;
            }
         

            // 2) Load correct model row
            if ($this->department === 'tyre') {
                $model = Tyre::find($collectionId);
            } elseif ($this->department === 'asset') {
                $model = Asset::find($collectionId);
            } elseif($this->department === 'inventory') { // inventory
                $model = Inventory::find($collectionId);
            }

            if (!$model) {
                continue;
            }

          

            // 3) How much is available on this exact row
            $rowQtyAvailable = (float)($model->balance);
            if ($rowQtyAvailable <= 0) {
                continue;
            }

              

            // 5) Row cost in company currency
            $rowCostCompany = $model->currency_id != $this->company->currency_id
                ? (float)$model->exchange_amount
                : (float)$model->total;

           

            $unitCost      = $rowCostCompany / $rowQtyAvailable;
          
            $qtyFromRow    = min($requestedQty, $rowQtyAvailable);
              
            $amountFromRow = $qtyFromRow * $unitCost;

           

            // 6) Create dispatch item
            $dispatch_item               = new DispatchItem;
            $dispatch_item->dispatch_id  = $dispatch->id;
            $dispatch_item->product_id   = $model->product?->id;

            if (isset($this->requestedItem[$key])) {
                $dispatch_item->ticket_request_id = $this->requestedItem[$key];
            }

            $dispatch_item->qty         = $qtyFromRow;
            $dispatch_item->unit_cost   = $unitCost;
            $dispatch_item->amount      = $amountFromRow;
            $dispatch_item->currency_id = $this->company->currency_id;

            if ($this->department === 'tyre') {
                $dispatch_item->tyre_id = $model->id;
            } elseif ($this->department === 'asset') {
                $dispatch_item->asset_id = $model->id;
            } else {
                $dispatch_item->inventory_id = $model->id;
            }

            $dispatch_item->save();

            // 8) Add to overall dispatch total
            $dispatch_total += $amountFromRow;
        }

        return $dispatch_total;
    }

    public function inventoryAVCO($dispatch)
    {
        $dispatch_total = 0;

        // 1) Resolve collection based on department
        if ($this->department === 'tyre') {
            $collection = $this->selectedTyre ?? [];
        } elseif ($this->department === 'asset') {
            $collection = $this->selectedAsset ?? [];
        } elseif ($this->department === 'inventory') {
            $collection = $this->selectedInventory ?? [];
        } else {
            return 0;
        }

        if (empty($collection)) {
            return 0;
        }

        foreach ($collection as $key => $collectionId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);
            if ($requestedQty <= 0) {
                continue;
            }

            // 2) Load correct model row
            if ($this->department === 'tyre') {
                $model = Tyre::find($collectionId);
            } elseif ($this->department === 'asset') {
                $model = Asset::find($collectionId);
            } elseif ($this->department === 'inventory') {
                $model = Inventory::find($collectionId);
            }

            if (!$model) {
                continue;
            }

            // 3) AVCO: gather ALL stock rows for this product to compute weighted average
            $productId = $model->product?->id;

            if ($this->department === 'tyre') {
                $allRows = Tyre::where('product_id', $productId)->where('balance', '>', 0)->get();
            } elseif ($this->department === 'asset') {
                $allRows = Asset::where('product_id', $productId)->where('balance', '>', 0)->get();
            } else {
                $allRows = Inventory::where('product_id', $productId)->where('balance', '>', 0)->get();
            }

            // 4) Calculate weighted average cost across all stock rows
            $totalQty  = 0;
            $totalCost = 0;

            foreach ($allRows as $row) {
                $rowQty = (float)$row->balance;
                $rowCost = $row->currency_id != $this->company->currency_id
                    ? (float)$row->exchange_amount
                    : (float)$row->total;

                $totalQty  += $rowQty;
                $totalCost += $rowCost;
            }

            if ($totalQty <= 0) {
                continue;
            }

            // 5) Weighted average unit cost
            $avgUnitCost = $totalCost / $totalQty;

            $rowQtyAvailable = (float)$model->balance;
            if ($rowQtyAvailable <= 0) {
                continue;
            }

            $qtyFromRow    = min($requestedQty, $rowQtyAvailable);
            $amountFromRow = $qtyFromRow * $avgUnitCost;

            // 6) Create dispatch item
            $dispatch_item              = new DispatchItem;
            $dispatch_item->dispatch_id = $dispatch->id;
            $dispatch_item->product_id  = $productId;

            if (isset($this->requestedItem[$key])) {
                $dispatch_item->ticket_request_id = $this->requestedItem[$key];
            }

            $dispatch_item->qty         = $qtyFromRow;
            $dispatch_item->unit_cost   = $avgUnitCost;
            $dispatch_item->amount      = $amountFromRow;
            $dispatch_item->currency_id = $this->company->currency_id;

            if ($this->department === 'tyre') {
                $dispatch_item->tyre_id = $model->id;
            } elseif ($this->department === 'asset') {
                $dispatch_item->asset_id = $model->id;
            } else {
                $dispatch_item->inventory_id = $model->id;
            }

            $dispatch_item->save();

            // 7) Add to overall dispatch total
            $dispatch_total += $amountFromRow;
        }

        return $dispatch_total;
    }


    public function ProductFIFO($dispatch){
       

        $dispatch_total = 0.0;

        if (empty($this->selectedProduct)) {
            return 0.0;
        }

            foreach ($this->selectedProduct as $key => $productId) {

                $requestedQty = (float)($this->qty[$key] ?? 0); // in litres/units to dispatch
                if ($requestedQty <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                // 1) Get source rows in FIFO order
                $items = collect();

                switch ($this->department) {
                    case 'inventory':
                        $items = Inventory::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)  // use balance as availability
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;

                    case 'asset':
                        $items = Asset::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;

                    case 'tyre':
                        $items = Tyre::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;
                }

                if ($items->isEmpty()) {
                    continue;
                }
               
                $remainingQty        = $requestedQty;
                $totalQtyDispatched  = 0.0;
                $totalLineAmount     = 0.0;

                foreach ($items as $item) {

                    if ($remainingQty <= 0) {
                        break; // request satisfied
                    }

                    // --- 2) Determine how much is available on this row ---
                    
                    if ($this->department === 'inventory') {
                        // Liquids / contents:
                        // balance = remaining litres, weight = original litres (capacity)
                        $rowQtyAvailable = (float)$item->balance;
                        $rowCapacity     = (float)($item->balance); 
                    } else {
                        // For assets/tyres etc. you can treat balance as remaining units
                        $rowQtyAvailable = (float)($item->balance ?: $item->qty);
                        $rowCapacity     = (float)($rowQtyAvailable);
                    }

                    if ($rowQtyAvailable <= 0 || $rowCapacity <= 0) {
                        continue;
                    }

                    // --- 3) Row cost in company currency ---
                    $rowCostCompany = $item->currency_id != $this->company->currency_id
                        ? (float)$item->exchange_amount   // already converted
                        : (float)$item->total;            // native in company currency
                   
                    // Unit cost per litre/unit from THIS row
                    $unitCost = $rowCostCompany / $rowQtyAvailable;
                   

                    // --- 4) How much do we take from this row (FIFO) ---
                    $qtyFromRow = min($remainingQty, $rowQtyAvailable);
                   
                    // Amount for this portion
                    $amountFromRow = $qtyFromRow * $unitCost;
                    
                    // --- 5) Create a dispatch line referencing this source row ---
                    $dispatch_item               = new DispatchItem;
                    $dispatch_item->dispatch_id  = $dispatch->id;
                    if(isset($this->requestedItem[$key])){
                        $dispatch_item->ticket_request_id  = $this->requestedItem[$key];
                    }
                    $dispatch_item->product_id   = $product?->id;
                    $dispatch_item->qty          = $qtyFromRow;       // litres/units taken
                    $dispatch_item->unit_cost   = $unitCost;
                    $dispatch_item->amount       = $amountFromRow;
                    $dispatch_item->currency_id  = $this->company->currency_id;

                    // Link to the source row for later authorization reduction
                    if ($this->department === 'inventory') {
                        $dispatch_item->inventory_id = $item->id;
                    } elseif ($this->department === 'asset') {
                        $dispatch_item->asset_id = $item->id;
                    } elseif ($this->department === 'tyre') {
                        $dispatch_item->tyre_id = $item->id;
                    }

                    $dispatch_item->save();

                    // --- 6) Track totals on this dispatch ---
                    $totalQtyDispatched += $qtyFromRow;
                    $totalLineAmount    += $amountFromRow;
                    $remainingQty       -= $qtyFromRow;
                   
                }

                // If nothing could be dispatched, skip
                if ($totalQtyDispatched <= 0) {
                    continue;
                }

                // Add to overall dispatch total
                 $dispatch_total += $totalLineAmount;

                
            }

        return $dispatch_total;
    
    }


    public function ProductAVCO($dispatch){

        $dispatch_total = 0;

           if ($this->selectedProduct) {

                    foreach ($this->selectedProduct as $key => $productId) {

                        $requestedQty = (int)($this->qty[$key] ?? 0); // requested / intended qty
                        if ($requestedQty < 1) {
                            continue;
                        }

                        $product = Product::find($productId);
                        if (!$product) {
                            continue;
                        }

                        $items = collect();

                        switch ($this->department) {
                            case 'inventory':
                                $items = Inventory::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->where('balance', '>', 0)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;

                            case 'asset':
                                $items = Asset::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->where('balance', '>', 0)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;

                            case 'tyre':
                                $items = Tyre::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;
                        }

                        if ($items->isEmpty()) {
                            continue;
                        }

                        // 1) Work out total available quantity & total cost in company currency
                        $totalQtyAvailable    = 0;
                        $totalCostCompanyCurr = 0.0;

                        foreach ($items as $item) {

                            // how many units does this row represent?
                            if ($this->department === 'inventory') {
                                // assume "balance" is the remaining litres/units
                                $itemQty = (float)$item->balance;
                            } else {
                                // asset / tyre: each row = 1 unit
                                $itemQty = 1.0;
                            }

                            if ($itemQty <= 0) {
                                continue;
                            }

                            // cost of this row in company currency
                            $rowCostCompany = $item->currency_id != $this->company->currency_id
                                ? (float)$item->exchange_amount
                                : (float)$item->total;

                            $totalQtyAvailable    += $itemQty;
                            $totalCostCompanyCurr += $rowCostCompany;
                        }

                        if ($totalQtyAvailable <= 0) {
                            continue;
                        }

                        // if user asks for more than available, either:
                        // - cap it, or
                        // - throw validation error. For now, cap it.
                        $dispatchQty = min($requestedQty, (int)$totalQtyAvailable);

                        // 2) Weighted average unit cost
                        $averageUnitCost = $totalCostCompanyCurr / $totalQtyAvailable;

                        // 3) Line total for this dispatch
                        $lineTotal = $averageUnitCost * $dispatchQty;

                        // 4) Create ONE dispatch line
                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id  = $dispatch->id;
                        $dispatch_item->product_id   = $product->id;
                        $dispatch_item->qty          = $dispatchQty;
                        $dispatch_item->unit_cost   = $averageUnitCost;
                        $dispatch_item->amount       = $lineTotal;
                        $dispatch_item->currency_id  = $this->company->currency_id;
                        $dispatch_item->save();

                        // 5) Update dispatch total
                        $dispatch_total += $lineTotal;

                        // ⚠️ NOTE:
                        // You still need separate logic to reduce Inventory/Asset/Tyre balances
                        // in FIFO order or however you decide to consume them physically.
                    }
                }
    }