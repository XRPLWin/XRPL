'use strict';
const _ = require('lodash');
const BASE_LEDGER_INDEX = 8819951;

function getMarkerAndLinesFromRequest(request) {
  const itemCount = 401; // Items on the ledger
  const perRequestLimit = 400;
  const pageCount = Math.ceil(itemCount / perRequestLimit);

  // marker is the index of the next item to return
  const startIndex = request.marker ? Number(request.marker) : 0;

  // No minimum: there are only a certain number of results on the ledger.
  // Maximum: the lowest of (perRequestLimit, itemCount - startIndex, request.limit).
  const lineCount = Math.min(perRequestLimit, itemCount - startIndex, request.limit);

  const trustline = {
    account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
    balance: '0.3488146605801446',
    currency: 'CHF',
    limit: '0',
    limit_peer: '0',
    quality_in: 0,
    quality_out: 0
  };

  return {
    marker: itemCount - lineCount > 0 ? startIndex + lineCount : undefined,
    lines: new Array(lineCount).fill(trustline)
  };
}

module.exports.normal = function(request, options = {}) {
  _.defaults(options, {
    ledger: BASE_LEDGER_INDEX
  });

  return {
    id: request.id,
    status: 'success',
    type: 'response',
    result: {
      account: request.account,
      marker: options.marker,
      limit: request.limit,
      ledger_index: options.ledger,
      lines: [{
        account: 'r3vi7mWxru9rJCxETCyA1CHvzL96eZWx5z',
        balance: '0',
        currency: 'ASP',
        limit: '0',
        limit_peer: '10',
        quality_in: 1000000000,
        quality_out: 0
      },
      {
        account: 'r3vi7mWxru9rJCxETCyA1CHvzL96eZWx5z',
        balance: '0',
        currency: 'XAU',
        limit: '0',
        limit_peer: '0',
        no_ripple: true,
        no_ripple_peer: true,
        quality_in: 0,
        quality_out: 0,
        freeze: true
      },
      {
        account: 'rMwjYedjc7qqtKYVLiAccJSmCwih4LnE2q',
        balance: '2.497605752725159',
        currency: 'USD',
        limit: '5',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0,
        freeze: true
      },
      {
        account: 'rHpXfibHgSb64n8kK9QWDpdbfqSpYbM9a4',
        balance: '481.992867407479',
        currency: 'MXN',
        limit: '1000',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rLEsXccBGNR3UPuPu2hUXPjziKC3qKSBun',
        balance: '0.793598266778297',
        currency: 'EUR',
        limit: '1',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rnuF96W4SZoCJmbHYBFoJZpR8eCaxNvekK',
        balance: '0',
        currency: 'CNY',
        limit: '3',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rGwUWgN5BEg3QGNY3RX2HfYowjUTZdid3E',
        balance: '1.294889190631542',
        currency: 'DYM',
        limit: '3',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '0.3488146605801446',
        currency: 'CHF',
        limit: '0',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '2.114103174931847',
        currency: 'BTC',
        limit: '3',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '0',
        currency: 'USD',
        limit: '5000',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rpgKWEmNqSDAGFhy5WDnsyPqfQxbWxKeVd',
        balance: '-0.00111',
        currency: 'BTC',
        limit: '0',
        limit_peer: '10',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rBJ3YjwXi2MGbg7GVLuTXUWQ8DjL7tDXh4',
        balance: '-0.1010780000080207',
        currency: 'BTC',
        limit: '0',
        limit_peer: '10',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rLEsXccBGNR3UPuPu2hUXPjziKC3qKSBun',
        balance: '1',
        currency: 'USD',
        limit: '1',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'razqQKzJRdB4UxFPWf5NEpEG3WMkmwgcXA',
        balance: '8.07619790068559',
        currency: 'CNY',
        limit: '100',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '7.292695098901099',
        currency: 'JPY',
        limit: '0',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'r3vi7mWxru9rJCxETCyA1CHvzL96eZWx5z',
        balance: '0',
        currency: 'AUX',
        limit: '0',
        limit_peer: '0',
        no_ripple: true,
        no_ripple_peer: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'r9vbV3EHvXWjSkeQ6CAcYVPGeq7TuiXY2X',
        balance: '0',
        currency: 'USD',
        limit: '1',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '12.41688780720394',
        currency: 'EUR',
        limit: '100',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rfF3PNkwkq1DygW2wum2HK3RGfgkJjdPVD',
        balance: '35',
        currency: 'USD',
        limit: '500',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rwUVoVMSURqNyvocPCcvLu3ygJzZyw8qwp',
        balance: '-5',
        currency: 'JOE',
        limit: '0',
        limit_peer: '50',
        no_ripple_peer: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rE6R3DWF9fBD7CyiQciePF9SqK58Ubp8o2',
        balance: '0',
        currency: 'USD',
        limit: '0',
        limit_peer: '100',
        no_ripple_peer: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rE6R3DWF9fBD7CyiQciePF9SqK58Ubp8o2',
        balance: '0',
        currency: 'JOE',
        limit: '0',
        limit_peer: '100',
        no_ripple_peer: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rs9M85karFkCRjvc6KMWn8Coigm9cbcgcx',
        balance: '0',
        currency: '015841551A748AD2C1F76FF6ECB0CCCD00000000',
        limit: '10.01037626125837',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rEhDDUUNxpXgEHVJtC2cjXAgyx5VCFxdMF',
        balance: '0',
        currency: 'USD',
        limit: '0',
        limit_peer: '1',
        quality_in: 0,
        quality_out: 0,
        freeze: true
      }
      ].filter(item => !request.peer || item.account === request.peer)
    }
  };
};

module.exports.counterparty = function(request, options = {}) {
  _.defaults(options, {
    ledger: BASE_LEDGER_INDEX
  });

  return {
    id: request.id,
    status: 'success',
    type: 'response',
    result: {
      account: request.account,
      marker: options.marker,
      limit: request.limit,
      ledger_index: options.ledger,
      lines: [{
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '0.3488146605801446',
        currency: 'CHF',
        limit: '0',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '2.114103174931847',
        currency: 'BTC',
        limit: '3',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '0',
        currency: 'USD',
        limit: '5000',
        limit_peer: '0',
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '7.292695098901099',
        currency: 'JPY',
        limit: '0',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      },
      {
        account: 'rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B',
        balance: '12.41688780720394',
        currency: 'EUR',
        limit: '100',
        limit_peer: '0',
        no_ripple: true,
        quality_in: 0,
        quality_out: 0
      }
      ]
    }
  };
};

module.exports.manyItems = function(request, options = {}) {
  _.defaults(options, {
    ledger: BASE_LEDGER_INDEX
  });

  const {marker, lines} = getMarkerAndLinesFromRequest(request);

  return {
    id: request.id,
    status: 'success',
    type: 'response',
    result: {
      account: request.account,
      marker,
      limit: request.limit,
      ledger_index: options.ledger,
      lines
    }
  };
};


module.exports.ripplingDisabled = function(request, options = {}) {
  _.defaults(options, {
    ledger: BASE_LEDGER_INDEX
  });

  return {
    id: request.id,
    status: 'success',
    type: 'response',
    result: {
      account: request.account,
      marker: options.marker,
      limit: request.limit,
      ledger_index: options.ledger,
      lines: [{'account': 'rEyiXgWXCKsh9wXYRrXCYSgCbR1gj3Xd8b',
            'balance': '0',
            'currency': 'ETH',
            'limit': '10000000000',
            'limit_peer': '0',
            'no_ripple': true,
            'no_ripple_peer': true,
            'quality_in': 0,
            'quality_out': 0},
           {'account': 'rEyiXgWXCKsh9wXYRrXCYSgCbR1gj3Xd8b',
            'balance': '0',
            'currency': 'BTC',
            'limit': '10000000000',
            'limit_peer': '0',
            'no_ripple': false,
            'no_ripple_peer': true,
            'quality_in': 0,
            'quality_out': 0}]
    }
  };
};