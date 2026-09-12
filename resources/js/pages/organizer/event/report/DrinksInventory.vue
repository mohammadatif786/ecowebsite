<template>
  <OrganizerLayout>
    <div class="max-w-7xl mx-auto p-6 space-y-6 font-sans">

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Total Items</div>
          <div class="text-xl font-bold mt-1">{{ summary.totalItems }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Low Stock (&lt;10)</div>
          <div class="text-xl font-bold mt-1 text-red-600">{{ summary.lowStock }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Total Variance</div>
          <div class="text-xl font-bold mt-1" :class="summary.variance < 0 ? 'text-red-600' : ''">{{ summary.variance }}
          </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Visible Rows</div>
          <div class="text-xl font-bold mt-1">{{ summary.visibleRows }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Gross Sales</div>
          <div class="text-xl font-bold mt-1 text-green-600">{{ formatCurrency(summary.gross) }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Drink/Bottle Fees</div>
          <div class="text-xl font-bold mt-1 text-orange-600">{{ formatCurrency(summary.fees) }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center shadow-sm">
          <div class="text-xs text-gray-500">Net Sales</div>
          <div class="text-xl font-bold mt-1 text-green-700">{{ formatCurrency(summary.net) }}</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200">
        <button v-for="tab in tabs" :key="tab.id" @click="setActiveTab(tab.id)" :class="[
          'px-4 py-2 font-medium transition rounded-t-lg',
          activeTab === tab.id
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]">
          {{ tab.label }}
        </button>
      </div>

      <!-- Individual Drinks -->
      <div v-show="activeTab === 'individual'" class="space-y-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700">Category</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Price</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Forecast</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Actual</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Remaining</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Variance</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="drink in filteredDrinks" :key="drink.id">
                <td class="px-4 py-3 text-sm">{{ drink.category }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ drink.name }}</td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ formatCurrency(drink.price) }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ drink.forecast }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ drink.actual }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    drink.remaining < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                    {{ drink.remaining }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-right"
                  :class="drink.variance < 0 ? 'text-red-600' : 'text-green-600'">
                  {{ drink.variance > 0 ? '+' : '' }}{{ drink.variance }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
          <canvas ref="chartIndividual"></canvas>
        </div>
      </div>

      <!-- Bottle Service -->
      <div v-show="activeTab === 'bottle'" class="space-y-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Price</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Forecast</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Actual</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Remaining</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700">Variance</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="bottle in filteredBottles" :key="bottle.id">
                <td class="px-4 py-3 text-sm">{{ bottle.category }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ bottle.name }}</td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ bottle.price }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ bottle.forecast }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  {{ bottle.actual }}
                </td>
                <td class="px-4 py-3 text-sm text-right">
                  <span
                    :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', bottle.remaining < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                    {{ bottle.remaining }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-right" :class="bottle.variance < 0 ? 'text-red-600' : ''">
                  {{ bottle.variance > 0 ? '+' : '' }}{{ bottle.variance }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
          <canvas ref="chartBottle"></canvas>
        </div>
      </div>

      <!-- Suggested Purchase List -->
      <div v-show="activeTab === 'suggest'" class="space-y-8">
        <div class="flex gap-2 print:hidden">
          <button @click="printAll"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">Print All</button>
          <button @click="printIndividuals"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Print
            Individuals</button>
          <button @click="printBottle"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Print Bottle</button>
        </div>

        <!-- Individual -->
        <div data-print-scope id="print-individual">
          <h2 class="text-lg font-bold mb-2">Suggested Purchase List — Individual Drinks</h2>
          <p class="text-sm text-gray-600 mb-4">Rule: <em>Suggested Purchase Qty = Actual Sold + 20% Contingency</em>
          </p>
          <table class="min-w-full border border-gray-300 bg-white">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Category</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Forecasted</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Required</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Suggested</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in suggested.individual" :key="item.id">
                <td class="px-4 py-2 text-sm">{{ item.category }}</td>
                <td class="px-4 py-2 text-sm font-medium">{{ item.name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ item.forecast }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ item.required }}</td>
                <td class="px-4 py-2 text-sm text-right font-bold">{{ item.required }}</td>
                <td class="px-4 py-2 text-center">
                  <span :class="item.required < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                    class="inline-block px-2 py-1 rounded-full text-xs font-medium">
                    {{ item.required < 10 ? 'Low' : 'OK' }} </span>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="font-bold bg-gray-50">
                <th colspan="4" class="px-4 py-2 text-right">Total Suggested Units (Individual)</th>
                <th class="px-4 py-2 text-right">{{suggested.individual.reduce((s, i) => s + i.required, 0)}}</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Bottle -->
        <div data-print-scope id="print-bottle">
          <h2 class="text-lg font-bold mb-2 mt-8">Suggested Purchase List — Bottle Service</h2>
          <table class="min-w-full border border-gray-300 bg-white">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Forecasted</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Required</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Suggested</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in suggested.bottle" :key="item.id">
                <td class="px-4 py-2 text-sm">{{ item.type }}</td>
                <td class="px-4 py-2 text-sm font-medium">{{ item.name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ item.forecast }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ item.required }}</td>
                <td class="px-4 py-2 text-sm text-right font-bold">{{ item.required }}</td>
                <td class="px-4 py-2 text-center">
                  <span :class="item.required < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                    class="inline-block px-2 py-1 rounded-full text-xs font-medium">
                    {{ item.required < 10 ? 'Low' : 'OK' }} </span>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="font-bold bg-gray-50">
                <th colspan="4" class="px-4 py-2 text-right">Total Suggested Units (Bottle)</th>
                <th class="px-4 py-2 text-right">{{suggested.bottle.reduce((s, i) => s + i.required, 0)}}</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Revenue Report -->
      <div v-show="activeTab === 'revenue'" class="space-y-6">
        <div class="flex gap-2 print:hidden">
          <button @click="printRevenueAll"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">Print All
            (Revenue)</button>
          <button @click="printRevenueIndividuals"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Print
            Individuals</button>
          <button @click="printRevenueBottle"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Print Bottle</button>
          <button @click="printRevenueTable"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Print Table</button>
        </div>

        <div data-print-scope id="print-revenue-individual"
          class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center gap-2">
            <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
              class="object-contain rounded-lg log_image" style="width: 70px; height: 70px;" />
            <h1 class="text-sm font-bold overall-title">LinkUp Event Revenue Report</h1>
          </div>

          <h1 class=" text-lg font-bold mb-1 individual-title">LinkUp Event Individual Drinks Report</h1>
          <h2 class="text-lg font-bold mb-1 event_title">{{ event.title }}</h2>
          <h3 class="text-lg font-bold mb-4">Individual Drinks (per item)</h3>
          <p class="text-xs text-gray-500 mb-3">
            Gross = customer spend (price × sold + drink/bottle fees). Net = organiser revenue (price × sold).
          </p>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Price</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Sold</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Gross</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Drink Fee</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Bottle Fee</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Total Fees</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Net</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="r in revenue.individual" :key="r.id">
                <td class="px-4 py-2 text-sm font-medium">{{ r.name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(r.price) }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ r.actual }}</td>
                <td class="px-4 py-2 text-sm text-right font-medium">{{ formatCurrency(r.gross) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.drinkFee) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.bottleFee) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.totalFees) }}</td>
                <td class="px-4 py-2 text-sm text-right text-green-700">{{ formatCurrency(r.net) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="font-bold bg-gray-50">
                <th colspan="3" class="px-4 py-2 text-right">Totals (Individual)</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.individual.reduce((s, r) => s + r.gross, 0))}}
                </th>
                <th class="px-4 py-2 text-right"> {{
                  formatCurrency(revenue.individual.reduce((s, r) => s + r.drinkFee,
                    0))
                }}</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.individual.reduce((s, r) => s + r.bottleFee,
                  0))}}</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.individual.reduce((s, r) => s + r.totalFees,
                  0))}}</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.individual.reduce((s, r) => s + r.net, 0))}}
                </th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div data-print-scope id="print-revenue-bottle"
          class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center gap-2">
            <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
              class="object-contain rounded-lg log_image" style="width: 70px; height: 70px;" />
            <h1 class="text-sm font-bold bottle-title">LinkUp Event Bottle Report</h1>
            <h2 class="text-lg font-bold mb-1 event_title">{{ event.title }}</h2>
          </div>
          <h3 class="text-lg font-bold mb-4">Bottle Service (per item)</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Price</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Sold</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Gross</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Drink Fee</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Bottle Fee</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Total Fees</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Net</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="r in revenue.bottle" :key="r.id">
                <td class="px-4 py-2 text-sm font-medium">{{ r.name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(r.price) }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ r.actual }}</td>
                <td class="px-4 py-2 text-sm text-right font-medium">{{ formatCurrency(r.gross) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.drinkFee) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.bottleFee) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.totalFees) }}</td>
                <td class="px-4 py-2 text-sm text-right text-green-700">{{ formatCurrency(r.net) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="font-bold bg-gray-50">
                <th colspan="3" class="px-4 py-2 text-right">Totals (Bottle)</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.bottle.reduce((s, r) => s + r.gross, 0))}}
                </th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.bottle.reduce((s, r) => s + r.drinkFee, 0))}}
                </th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.bottle.reduce((s, r) => s + r.bottleFee, 0))}}
                </th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.bottle.reduce((s, r) => s + r.totalFees, 0))}}
                </th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.bottle.reduce((s, r) => s + r.net, 0))}}
                </th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div data-print-scope id="print-revenue-table" class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center gap-2">
            <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
              class="object-contain rounded-lg log_image" style="width: 70px; height: 70px;" />
            <h1 class="text-sm font-bold table-title">LinkUp Event Table Report</h1>
          </div>
          <h2 class="text-lg font-bold mb-1 event_title">{{ event.title }}</h2>
          <h3 class="text-lg font-bold mb-4 table-title">Table Report</h3>
          <p class="text-xs text-gray-500 mb-3">
            VIP Fee rule: Applied to table sales .
            Fee = vip_fee_pct × subtotal. This fee is shown in Fees and is passed to the customer; organiser Net is the
            subtotal.
          </p>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Name</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Price</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Sold</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Fees</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Net</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="r in revenue.table" :key="r.id">
                <td class="px-4 py-2 text-sm font-medium">{{ r.name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(r.price) }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ r.sold }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(r.totalFees) }}</td>
                <td class="px-4 py-2 text-sm text-right text-green-700">{{ formatCurrency(r.net) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="font-bold bg-gray-50">
                <th colspan="3" class="px-4 py-2 text-right">Totals (Table)</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.table.reduce((s, r) => s +
                  (Number(r.totalFees) || 0), 0))}}</th>
                <th class="px-4 py-2 text-right">{{formatCurrency(revenue.table.reduce((s, r) => s + (Number(r.net) ||
                  0), 0))}}</th>
              </tr>
            </tfoot>
          </table>
        </div>
        <div data-print-scope id="print-revenue-overall"
          class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <h3 class="text-lg font-bold mb-2">Overall Totals</h3>
          <p class="text-lg">Gross (All): <strong>{{ formatCurrency(revenue.totals.gross) }}</strong> | Fees: <strong>{{
            formatCurrency(revenue.totals.fees) }}</strong> | Net: <strong>{{ formatCurrency(revenue.totals.net)
              }}</strong></p>
          <p class="mt-2"><strong>Top Selling Drink:</strong> {{ revenue.top ? `${revenue.top.name} —
            ${revenue.top.actual} sold (${formatCurrency(revenue.top.gross)} gross)` : '—' }}</p>
        </div>
      </div>

      <!-- Table Package Report -->
      <div v-show="activeTab === 'table_package'" class="space-y-6">
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <h3 class="text-lg font-bold mb-1">Table Package</h3>
          <p class="text-xs text-gray-500 mb-3">
            Price = organiser revenue per package sale. VIP Fee uses <code>vip_fee_pct</code>. Gross = Price + VIP Fee.
          </p>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Date / Time</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Ticket</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Package</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Price (Net)</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">VIP Fee</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Gross</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="r in vipPackageSales" :key="r.id">
                <td class="px-4 py-2 text-sm font-medium">{{ formatDateTime(r.created_at) }}</td>
                <td class="px-4 py-2 text-sm font-medium">{{ r.ticket_name }}</td>
                <td class="px-4 py-2 text-sm">{{ r.package_name }}</td>
                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(r.subtotal) }}</td>
                <td class="px-4 py-2 text-sm text-right text-orange-600">{{ formatCurrency(calcVipFee(r.subtotal,
                  r.package_name)) }}
                </td>
                <td class="px-4 py-2 text-sm text-right font-medium">{{ formatCurrency(calcVipGross(r.subtotal,
                  r.package_name)) }}</td>
              </tr>
              <tr v-if="!vipPackageSales || vipPackageSales.length === 0">
                <td class="px-4 py-3 text-sm text-gray-500" colspan="5">No VIP package sales found.</td>
              </tr>
            </tbody>
            <tfoot v-if="vipPackageSales && vipPackageSales.length > 0">
              <tr class="font-bold bg-gray-50">
                <th colspan="3" class="px-4 py-2 text-right">Totals</th>
                <th class="px-4 py-2 text-right">
                  {{formatCurrency(vipPackageSales.reduce((s, r) => s + (Number(r.subtotal) || 0), 0))}}
                </th>
                <th class="px-4 py-2 text-right text-orange-600">
                  {{formatCurrency(vipPackageSales.reduce((s, r) => s + calcVipFee(r.subtotal, r.package_name), 0))}}
                </th>
                <th class="px-4 py-2 text-right">
                  {{formatCurrency(vipPackageSales.reduce((s, r) => s + calcVipGross(r.subtotal, r.package_name), 0))}}
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Table Package Drinks Report -->
      <div v-show="activeTab === 'table_package_drinks'" class="space-y-6">
        <div class="flex gap-2 print:hidden">
          <button @click="printTablePackageDrinks"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">Print</button>
        </div>
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm" data-print-scope
          id="print-table-package-drinks">
          <div class="flex items-center gap-2">
            <img :src="'/storage/events/logo_for_e_tickets.png'" alt="E-Tickets Logo"
              class="object-contain rounded-lg log_image" style="width: 70px; height: 70px;" />
            <h1 class="text-lg font-bold table-drinks-title">LinkUp Event Table Drinks Report</h1>
          </div>
          <h2 class="text-lg font-bold mb-1 event_title">{{ event.title }}</h2>
          <h3 class="text-lg font-bold mb-1">Table Package Drinks</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Date / Time</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Ticket</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Package</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Drinks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="r in tablePackageDrinks" :key="r.id">
                <td class="px-4 py-2 text-sm font-medium">{{ formatDateTime(r.created_at) }}</td>
                <td class="px-4 py-2 text-sm font-medium">{{ r.ticket_name }}</td>
                <td class="px-4 py-2 text-sm">{{ r.package_name }}</td>
                <td class="px-4 py-2 text-sm text-right">
                  <table class="min-w-full border border-gray-300 rounded">
                    <thead class="bg-gray-100 text-xs">
                      <tr>
                        <th class="border px-2 py-1">Type</th>
                        <th class="border px-2 py-1">Name</th>
                        <th class="border px-2 py-1">Qty</th>
                      </tr>
                    </thead>
                    <tbody class="text-xs">
                      <tr v-for="(d, i) in formatDrinksTable(r.drinks, r.table_drink_addons)" :key="i">
                        <td class="border px-2 py-1">{{ d.type }}</td>
                        <td class="border px-2 py-1">{{ d.name }}</td>
                        <td class="border px-2 py-1 text-center">{{ d.qty }}</td>
                      </tr>
                    </tbody>
                  </table>

                </td>

              </tr>
              <tr v-if="!tablePackageDrinks || tablePackageDrinks.length === 0">
                <td class="px-4 py-3 text-sm text-gray-500" colspan="5">No table package drinks sales found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </OrganizerLayout>
</template>

<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/AppLayout.vue';
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

// Get the props from the server
const props = defineProps({
  individualDrinks: {
    type: Array,
    default: () => []
  },
  bottleService: {
    type: Array,
    default: () => []
  },
  drinkCategories: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({
      totalItems: 0,
      lowStock: 0,
      variance: 0,
      visibleRows: 0,
      gross: 0,
      fees: 0,
      net: 0
    })
  },
  event: {
    type: Object,
    default: () => ({})
  },
  allFees: {
    type: Object,
    default: () => ({})
  },
  vipPackageSales: {
    type: Array,
    default: () => []
  },
  tablePackageDrinks: {
    type: Array,
    default: () => []
  }
});
// Create reactive versions of the data
const individualDrinks = ref([...props.individualDrinks]);
const bottleService = ref([...props.individualDrinks ? props.individualDrinks.filter(bottle => bottle.category === 'Bottles') : []]);
const drinkCategories = ref([...props.drinkCategories]);
const summary = reactive({ ...props.summary });
const vipPackageSales = ref([...props.vipPackageSales]);
const tablePackageDrinks = ref([...props.tablePackageDrinks]);

// Tabs
const activeTab = ref('individual');
const tabs = [
  { id: 'individual', label: 'Individual Drinks' },
  { id: 'bottle', label: 'Bottle Service' },
  { id: 'suggest', label: 'Suggested Purchase' },
  { id: 'revenue', label: 'Revenue Report' },
  { id: 'table_package', label: 'Table Package' },
  { id: 'table_package_drinks', label: 'Table Package Drinks' }
];

// Make sure the activeTab is one of the valid tab IDs
const setActiveTab = (tabId) => {
  if (tabs.some(tab => tab.id === tabId)) {
    activeTab.value = tabId;
  }
};

import Chart from 'chart.js/auto';

const platformFeePct = 10;

const feeRates = computed(() => ({
  drink_fee_pct: Number(props.allFees?.drink_fee_pct || 0),
  bottle_fee_pct: Number(props.allFees?.bottle_fee_pct || 0),
  vip_fee_pct: Number(props.allFees?.vip_fee_pct || 0),
}));

const calcVipFee = (subtotal, packageName) => {
  const base = Number(subtotal || 0);
  const pkg = String(packageName || '').toLowerCase().trim();
  const isVipPkg = (pkg === 'vip package');
  const rate = Number(feeRates.value.vip_fee_pct || 0);
  return base * (rate / 100);
};

const calcVipGross = (subtotal, packageName) => {
  const base = Number(subtotal || 0);
  return base + calcVipFee(base, packageName);
};

const isBottleItem = (item) => {
  return (item?.category === 'Bottles') || (item?.type === 'Bottle') || /bottle/i.test(item?.category || '');
};

const calcItemFees = (item) => {
  const base = Number(item?.price || 0) * Number(item?.actual || 0);
  const isBottle = isBottleItem(item);
  const feeRate = isBottle ? feeRates.value.bottle_fee_pct : feeRates.value.drink_fee_pct;
  const feeAmount = base * (feeRate / 100);
  const drinkFee = isBottle ? 0 : feeAmount;
  const bottleFee = isBottle ? feeAmount : 0;
  const vipFee = 0;
  const totalFees = drinkFee + bottleFee + vipFee;
  const net = base;
  const gross = base + totalFees;
  return { gross, drinkFee, bottleFee, vipFee, totalFees, net };
};
const recalcItem = (item) => {
  item.required = Math.ceil(Number(item.forecast || 0) * 1.2);

  item.remaining = Math.max(0, Number(item.forecast || 0) - Number(item.actual || 0));

  item.variance = Number(item.forecast || 0) - Number(item.actual || 0);

  return item;
};

const recalcAll = () => {
  individualDrinks.value = individualDrinks.value.map(recalcItem);
  bottleService.value = bottleService.value.map(recalcItem);
  updateSummary();
  updateCharts();
};

const updateSummary = () => {
  const nonBottleIndividual = (individualDrinks.value || []).filter(d => d.category !== 'Bottles');
  const totalItems = nonBottleIndividual.length + bottleService.value.length;
  // Use summary values from API instead of recalculating
  // const lowStock = individualDrinks.value.filter(d => d.remaining < 10).length +
  //   bottleService.value.filter(b => b.remaining < 10).length;
  // const totalVariance = [...individualDrinks.value, ...bottleService.value]
  //   .reduce((sum, item) => sum + item.variance, 0);

  const gross = calculateGross();
  const feeSumInd = nonBottleIndividual.reduce((sum: number, d: any) => sum + calcItemFees(d).totalFees, 0);
  const feeSumBot = (bottleService.value || []).reduce((sum: number, b: any) => sum + calcItemFees(b).totalFees, 0);
  const feeSumTab = (vipPackageSales.value || []).reduce((sum: number, r: any) => sum + calcVipFee(r.subtotal, r.package_name), 0);

  const fees = feeSumInd + feeSumBot + feeSumTab;

  const drinkNetSum = (nonBottleIndividual.reduce((sum: number, d: any) => sum + calcItemFees(d).net, 0)) +
    ((bottleService.value || []).reduce((sum: number, b: any) => sum + calcItemFees(b).net, 0));

  const tableNetSum = (vipPackageSales.value || []).reduce((sum: number, r: any) => sum + Number(r.subtotal || 0), 0);
  const net = drinkNetSum + tableNetSum;

  try {
    Object.assign(summary, {
      totalItems,
      // Use API summary values for lowStock and variance
      // lowStock,
      // variance: totalVariance,
      visibleRows: totalItems,
      gross,
      fees,
      net
    });
  } catch (error) {
    console.error('Error updating summary:', error);
  }
};

let stopTabWatch = null;

const calculateGross = () => {
  const nonBottleIndividual = (individualDrinks.value || []).filter(d => d.category !== 'Bottles');
  const individualTotal = nonBottleIndividual.reduce(
    (sum: number, d: any) => sum + calcItemFees(d).gross,
    0
  );
  const bottleTotal = (bottleService.value || []).reduce(
    (sum: number, b: any) => sum + calcItemFees(b).gross,
    0
  );

  return (Number(individualTotal) || 0) + (Number(bottleTotal) || 0);
};

const suggested = computed(() => ({
  individual: (individualDrinks.value || []).filter(i => i.category !== 'Bottles').map(i => {
    const actual = Number(i.actual || 0);
    const required = Math.ceil(actual * 1.2);
    return {
      id: i.id,
      category: i.category,
      name: i.name,
      forecast: actual,
      required,
      suggested: required,
      status: required < 10 ? 'Low' : 'OK'
    };
  }),
  bottle: (bottleService.value || []).map(i => {
    const actual = Number(i.actual || 0);
    const required = Math.ceil(actual * 1.2);
    return {
      id: i.id,
      type: i.category,
      name: i.name,
      forecast: actual,
      required,
      suggested: required,
      status: required < 10 ? 'Low' : 'OK'
    };
  }),
  table: (vipPackageSales.value || []).map(i => ({
    id: i.id,
    type: i.category,
    package_name: i.package_name,
    ticket_name: i.ticket_name,
  })),
}));

const revenue = computed(() => {
  const mapWithFees = (item) => {
    const f = calcItemFees(item);
    return { ...item, ...f };
  };

  const individual = (individualDrinks.value || []).filter(i => i.category !== 'Bottles').map(mapWithFees);
  const bottle = (bottleService.value || []).map(mapWithFees);

  const tableMap = {};
  (vipPackageSales.value || []).forEach(r => {
    const name = `${r.ticket_name || 'Ticket'} — ${r.package_name || 'Table'}`;
    const key = name;
    const unit = Number(r?.subtotal || 0);
    const vipRate = Number(feeRates.value.vip_fee_pct || 0);
    const unitVipFee = vipRate > 0 ? unit * (vipRate / 100) : 0;
    if (!tableMap[key]) {
      tableMap[key] = { id: key, name, price: unit, sold: 0, totalFees: 0, net: 0 };
    }
    tableMap[key].sold += 1;
    tableMap[key].totalFees += unitVipFee;
    tableMap[key].net += unit;
  });
  const table = Object.values(tableMap);

  const tableGross = (table || []).reduce((s: number, r: any) => s + (Number(r.net) || 0) + (Number(r.totalFees) || 0), 0);
  const tableFees = (table || []).reduce((s: number, r: any) => s + (Number(r.totalFees) || 0), 0);
  const tableNet = (table || []).reduce((s: number, r: any) => s + (Number(r.net) || 0), 0);

  const totals = {
    gross: individual.reduce((s: number, i: any) => s + i.gross, 0)
      + bottle.reduce((s: number, i: any) => s + i.gross, 0),
    fees: individual.reduce((s: number, i: any) => s + i.totalFees, 0)
      + bottle.reduce((s, i) => s + i.totalFees, 0)
      + tableFees,
  };

  totals.net = individual.reduce((s: number, i: any) => s + i.net, 0)
    + bottle.reduce((s: number, i: any) => s + i.net, 0)
    + tableNet;

  let top = null;
  individual.forEach(i => { if (!top || i.actual > top.actual) top = i; });
  bottle.forEach(i => { if (!top || i.actual > top.actual) top = i; });

  return { individual, bottle, table, totals, top, topSelling: [...individual, ...bottle].sort((a, b) => b.gross - a.gross).slice(0, 3) };
});

const chartIndividual = ref(null);
const chartBottle = ref(null);
let chart1 = null, chart2 = null;

const updateCharts = () => {
  const makeData = (items) => ({
    labels: items.map(i => i.name),
    forecast: items.map(i => i.forecast),
    actual: items.map(i => i.actual),
    fees: items.map(i => calcItemFees(i).totalFees),
    net: items.map(i => calcItemFees(i).net),
  });

  const dataInd = makeData(individualDrinks.value ? individualDrinks.value.filter(drinks => drinks.category != "Bottles") : []);
  const dataBot = makeData(bottleService.value);

  const config = (data) => ({
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        { label: 'Forecasted Units', data: data.forecast, backgroundColor: '#3B82F6', yAxisID: 'y' },
        { label: 'Sold Units', data: data.actual, backgroundColor: '#A3E635', yAxisID: 'y' },
        { label: 'Platform Fees ($)', data: data.fees, backgroundColor: '#F97316', yAxisID: 'y1' },
        { label: 'Net Sales ($)', data: data.net, backgroundColor: '#16A34A', yAxisID: 'y1' },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Units' } },
        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue ($)' } },
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const val = ctx.parsed.y;
              return /Fees|Net/.test(ctx.dataset.label) ? `${ctx.dataset.label}: $${val.toFixed(2)}` : `${ctx.dataset.label}: ${val}`;
            },
          },
        },
      },
    },
  });

  try {
    if (chart1) chart1.destroy();
    if (chartIndividual.value) chart1 = new Chart(chartIndividual.value, config(dataInd));

    if (chart2) chart2.destroy();
    if (chartBottle.value) chart2 = new Chart(chartBottle.value, config(dataBot));
  } catch (error) {
    console.error('Error updating charts:', error);
  }
};

// === Print ===
const openPrintWindow = (html) => {
  const win = window.open('', '_blank');
  if (!win) return;
  const styles = `<!doctype html><html><head><title>Print</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
      @page{ size: A4; margin: 1cm; }
      body{ font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; font-size: 12px; color: #111827; }
      h2{ font-size: 16px; margin: 0 0 8px 0; }
      h3{ font-size: 14px; margin: 16px 0 8px 0; }
      table{ width: 100%; border-collapse: collapse; }
      th{ background:#EFF6FF; text-align:left; font-size: 12px; color:#374151; }
      th,td{ border:1px solid #e2e8f0; padding:6px 8px; }
      thead{ display: table-header-group; }
      .page-break{ page-break-after: always; }
    </style>
  </head><body>`;
  const footer = '</body></html>';
  win.document.open();
  win.document.write(styles + html + footer);
  win.document.close();
  const triggerPrint = () => { try { win.focus(); win.print(); win.close(); } catch (e) { /* noop */ } };
  if (win.document.readyState === 'complete') {
    setTimeout(triggerPrint, 100);
  } else {
    win.addEventListener('load', () => setTimeout(triggerPrint, 100));
    win.document.addEventListener('DOMContentLoaded', () => setTimeout(triggerPrint, 100));
  }
};

const printAll = () => {
  const ind = document.querySelector('#print-individual');
  const bot = document.querySelector('#print-bottle');
  const tablePackageDrinks = document.querySelector("#print-table-package-drinks");

  if (!ind && !bot && !tablePackageDrinks) { window.print(); return; }
  const parts = [];
  if (ind) parts.push(ind.outerHTML || '');
  if (bot) parts.push(bot.outerHTML || '');
  if (tablePackageDrinks) parts.push(tablePackageDrinks.outerHTML || '');
  openPrintWindow(parts.join(''));
};
const printTablePackageDrinks = () => {
  const tablePackageDrinks = document.querySelector("#print-table-package-drinks");

  if (!tablePackageDrinks) { window.print(); return; }
  showEventTitlesFor(['#print-table-package-drinks']);
  const tableDrinkTitles = document.querySelectorAll('#print-table-package-drinks .table-drinks-title');
  tableDrinkTitles.forEach(el => {
    el.style.display = 'block';
    el.style.textAlign = 'center';
  });
  const parts = [];
  if (tablePackageDrinks) parts.push(tablePackageDrinks.outerHTML || '');
  openPrintWindow(parts.join(''));
};
const printIndividuals = () => printScope('#print-individual');
const printBottle = () => printScope('#print-bottle');

const printScope = (sel) => {
  const el = document.querySelector(sel);
  if (!el) { window.print(); return; }
  const html = el.innerHTML ? `<div>${el.innerHTML}</div>` : el.outerHTML;
  openPrintWindow(html);
};

const showEventTitlesFor = (selectors = []) => {
  const all = document.querySelectorAll('.event_title');
  all.forEach(el => {
    el.style.display = 'none';
  });
  selectors.forEach(selector => {
    const targets = document.querySelectorAll(`${selector} .event_title`);
    targets.forEach(el => {
      el.style.display = 'block';
      el.style.textAlign = 'center';
    });
  });
};

const toggleTitles = ({ overall = false, individual = false, bottle = false, table = false, tableDrinks = false }) => {
  const overallTitle = document.querySelector(".overall-title");
  const individualTitle = document.querySelector(".individual-title");
  const bottleTitle = document.querySelector(".bottle-title");
  const tableTitle = document.querySelector(".table-title");
  const log_image = document.querySelector(".log_image");

  log_image?.style && (log_image.style.display = 'block');
  overallTitle?.style && (overallTitle.style.textAlign = 'center');
  individualTitle?.style && (individualTitle.style.textAlign = 'center');
  bottleTitle?.style && (bottleTitle.style.textAlign = 'center');
  tableTitle?.style && (tableTitle.style.textAlign = 'center');

  overallTitle && (overallTitle.style.display = overall ? 'block' : 'none');
  individualTitle && (individualTitle.style.display = individual ? 'block' : 'none');
  bottleTitle && (bottleTitle.style.display = bottle ? 'block' : 'none');
  tableTitle && (tableTitle.style.display = table ? 'block' : 'none');

  const sections = [];
  if (overall || individual) sections.push('#print-revenue-individual');
  if (overall || bottle) sections.push('#print-revenue-bottle');
  if (overall || table) sections.push('#print-revenue-table');
  if (tableDrinks) sections.push('#print-table-package-drinks');

  if (overall) {
    document.querySelectorAll('.event_title').forEach(el => {
      el.style.display = 'none';
    });

    const firstTop = document.querySelector('#print-revenue-individual .event_title');
    if (firstTop) firstTop.style.display = 'block';
    if (firstTop) firstTop.style.textAlign = 'center';

  } else {
    showEventTitlesFor(sections);
  }
};
onMounted(() => {
  window.onafterprint = () => {
    document.querySelectorAll(
      '.overall-title, .individual-title, .bottle-title, .table-title, .event_title, .log_image'
    ).forEach(el => {
      el.style.display = 'none';
    });
  };
});



const buildRevenueSectionHTML = (element, { showLogo = false } = {}) => {
  if (!element) return '';
  const clone = element.cloneNode(true);
  const logos = clone.querySelectorAll('.log_image');
  logos.forEach((logo, idx) => {
    if (showLogo && idx === 0) {
      logo.style.display = 'block';
    } else {
      logo.remove();
    }
  });
  return clone.outerHTML || '';
};

const printRevenueAll = () => {
  toggleTitles({ overall: true });
  const parts = [];
  const ind = document.querySelector('#print-revenue-individual');
  const bot = document.querySelector('#print-revenue-bottle');
  const tbl = document.querySelector('#print-revenue-table');
  const ovt = document.querySelector('#print-revenue-overall');

  if (ind) parts.push(buildRevenueSectionHTML(ind, { showLogo: true }));
  if (bot) parts.push(buildRevenueSectionHTML(bot));
  if (tbl) parts.push(buildRevenueSectionHTML(tbl));
  if (ovt) parts.push(buildRevenueSectionHTML(ovt, { showLogo: !ind }));

  if (parts.length === 0) { window.print(); return; }
  openPrintWindow(parts.join(''));
};

const printRevenueIndividuals = () => {
  toggleTitles({ individual: true });
  printScope('#print-revenue-individual');
};

const printRevenueBottle = () => {
  toggleTitles({ bottle: true });
  printScope('#print-revenue-bottle');
};

const printRevenueTable = () => {
  toggleTitles({ table: true });
  printScope('#print-revenue-table');
};


const closeApp = () => {
  router.visit(route('organizer.event.report.attendees', { event: props.event.id }));
};
const formatCurrency = (n) => `$${(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatDateTime = (input) => {
  if (!input) return '';
  const d = new Date(input);
  if (isNaN(d)) return String(input);
  const weekday = d.toLocaleDateString(undefined, { weekday: 'short' });
  const month = d.toLocaleDateString(undefined, { month: 'long' });
  const year = d.getFullYear();
  const time = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
  return `${weekday} / ${month} / ${year} -- ${time}`;
};

onMounted(() => {
  recalcAll();

  stopTabWatch = watch(activeTab, (newTab) => {
    console.log('Tab changed to:', newTab);
    if (newTab) {
      updateSummary();

      nextTick(() => {
        updateCharts();
      });
    }
  }, { immediate: true });

  updateSummary();
  nextTick(updateCharts);
});

onUnmounted(() => {
  if (stopTabWatch) {
    stopTabWatch();
  }
});

const filteredDrinks = computed(() =>
  individualDrinks.value.filter(drink => drink.category !== 'Bottles')
);

const filteredBottles = computed(() =>
  individualDrinks.value.filter(drink => drink.category === 'Bottles')
);
const formatDrinksTable = (drinks, tableAddons) => {
  const rows = [];

  if (drinks) {
    (drinks.bottles || []).forEach(b => {
      rows.push({ type: "Bottle", name: b.name, qty: b.qty });
    });
    (drinks.chasers || []).forEach(c => {
      rows.push({ type: "Chaser", name: c.name, qty: c.qty });
    });
    (drinks.waters || []).forEach(w => {
      rows.push({ type: "Water", name: w.name, qty: w.qty });
    });
  }
  else if (tableAddons && Array.isArray(tableAddons)) {
    tableAddons.forEach(t => {
      rows.push({
        type: t.section,
        name: t.name,
        qty: t.quantity
      });
    });
  }

  return rows;
}
</script>

<style>
.overall-title {
  display: none;
}

.event_title {
  display: none;
}

.table-drinks-title {
  display: none;
}

.log_image {
  display: none;
}

.individual-title {
  display: none;
}

.bottle-title {
  display: none;
}

/* Print styles */
@media print {
  @page {
    size: A4;
    margin: 1cm;
  }

  body {
    font-size: 12pt;
    line-height: 1.3;
  }

  [data-print-scope] {
    display: none !important;
  }

  [data-print-scope].print-target {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    width: 100% !important;
    left: 0 !important;
    top: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    page-break-inside: avoid;
    break-inside: avoid;
  }

  .print-target table {
    width: 100% !important;
    border-collapse: collapse;
    margin: 10px 0;
  }

  .print-target th,
  .print-target td {
    padding: 6px 8px;
    border: 1px solid #e2e8f0;
  }

  .print-target thead {
    display: table-header-group;
  }

  .print-target h2,
  .print-target h3 {
    page-break-after: avoid;
    break-after: avoid;
  }

  body * {
    visibility: hidden !important;
  }

  /* Make only the chosen print section visible */
  [data-print-scope].print-target,
  [data-print-scope].print-target * {
    visibility: visible !important;
  }

  /* Position the chosen section at top-left for clean print */
  [data-print-scope].print-target {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
  }
}

/* Regular styles */
@media print {
  .print:hidden {
    display: none !important;
  }


  [data-print-scope]:not(.print-target) {
    display: none !important;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th,
  td {
    border: 1px solid #ccc;
    padding: 8px;
    font-size: 12px;
  }

  th {
    background-color: #f3f4f6;
  }
}

@media screen {

  .overall-title,
  .individual-title,
  .bottle-title,
  .table-title,
  .event_title,
  .log_image {
    display: none !important;
  }
}
</style>
