/**
 * Print the currency
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function CnrtPricingCurrency (props) {
    let symbol = '$';
    if(props.name === 'eur') {
        symbol = '€';
    }
    return (
        <small>{symbol} </small>
    )
}

/**
 * Print the billing cycle near the price
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function CnrtPricingBillingCycle (props) {
    let cycle = '/year';
    if(props.name === 'monthly') {
        cycle = '/month';
    }
    if(props.name === 'lifetime') {
        cycle = '';
    }
    return (
        <small>{cycle}</small>
    )
}

/**
 * Print the rows with the features
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */

function CnrtPricingPrintFeatures(props) {
    let numberOfSites = ' 1 website';
    if(props.license === 'plus') {
        numberOfSites = ' 5 websites'
    }
    if(props.license === 'enterprise') {
        numberOfSites = ' 30 websites'
    }
    return (
        <ul className="PT-Features">
            {props.cycle === 'annual' && (
                <li className="cnrt-pricing-table-feature"><strong>1 year</strong> of supports and updates <br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            {props.cycle === 'monthly' && (
                <li className="cnrt-pricing-table-feature"><strong>1 month</strong> of supports and updates <br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            {props.cycle === 'lifetime' && (
                <li className="cnrt-pricing-table-feature"><strong>Lifetime</strong> updates and support<br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            <li className="cnrt-pricing-table-feature">Mark single comment as read</li>
            <li className="cnrt-pricing-table-feature">Direct email support</li>
            {props.cycle === 'lifetime' && (
                <li className="cnrt-pricing-table-feature">We setup the plugin for free <br/>(only lifetime plans)</li>
            )}
        </ul>
    );
}

function CnrtPricingPrice(props) {
    let price     = '';
    let licenses  = 1;
    let pricingId = '';
    if(props.license === 'single') {
        if(props.currency === 'eur') {
            pricingId = 17325;
        } else {
            pricingId = 17322;
        }
        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '2.29'
            } else {
                //usd
                price = '2.49'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '49.99'
            } else {
                //usd
                price = '59.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '16.99'
            } else {
                //usd
                price = '19.99'
            }
        }
    }
    else if(props.license === 'plus') {
        licenses = 5;
        if(props.currency === 'eur') {
            pricingId = 17326;
        } else {
            pricingId = 17323;
        }

        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '4.19'
            } else {
                //usd
                price = '4.99'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '99.99'
            } else {
                //usd
                price = '119.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '33.99'
            } else {
                //usd
                price = '39.99'
            }
        }
    }
    else if(props.license === 'enterprise') {
        licenses = 30;
        if(props.currency === 'eur') {
            pricingId = 17327;
        } else {
            pricingId = 17324;
        }
        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '6.19'
            } else {
                //usd
                price = '7.49'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '149.99'
            } else {
                //usd
                price = '179.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '49.99'
            } else {
                //usd
                price = '59.99'
            }
        }
    }
    return (
        <div className="cnrt-pring-table-price">
            <CnrtPricingCurrency name={props.currency} />
            <span>{price}</span>
            <CnrtPricingBillingCycle name={props.cycle} />
            <CnrtPricingPriceDesc cycle={props.cycle} currency={props.currency} license={props.license}/>
            <p className="PT-CTA">
                <a href="#"
                   className="cnrt-buy-button"
                   onClick={(event) => {
                       CnrtPricingRedirect(props.cycle, licenses, props.currency, pricingId)
                       event.preventDefault();
                   }}
                >Buy now</a>
            </p>
        </div>
    );
}

/**
 * Print the monthly price for annual
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function CnrtPricingPriceDesc(props) {
    if(props.cycle === 'annual') {
        let price = '';
        if(props.license === 'plus') {
            if(props.currency === 'eur') {
                //eur
                price = '2.83'
            } else {
                //usd
                price = '3.33';
            }
        } else if(props.license === 'enterprise') {
            if(props.currency === 'eur') {
                //eur
                price = '4.16'
            } else {
                //usd
                price = '4.99'
            }
        }
        //single site price
        else{
            if(props.currency === 'eur') {
                //eur
                price = '1.41'
            } else {
                //usd
                price = '1.66'
            }
        }

        return (
            <p className="cnrt-pricing-table-price-desc">
                <CnrtPricingCurrency name={props.currency}/>
                {price} /month
            </p>
        );
    }
    return (
        <></>
    )
}

/**
 *
 * @param cycle
 * @param licenses
 * @param currency
 * @param pricingId
 *
 * @return void;
 */
function CnrtPricingRedirect (cycle, licenses, currency, pricingId) {
    const params = {
        plugin_id:     9260,
        billing_cycle: cycle,
        pricing_id:    pricingId,
        licenses:      licenses,
        id:            'cnrt_checkout',
        page:          'cnrt_settings_page-pricing',
        checkout:      'true',
        plan_id:       '15580',
        plan_name:     'pro',
        disable_licenses_selector: true,
        hide_billing_cycles: true,
        currency: currency
    };

    let paramsBody = [];
    for (let property in params) {
        let encodedKey   = encodeURIComponent(property);
        let encodedValue = encodeURIComponent(params[property]);
        paramsBody.push(encodedKey + "=" + encodedValue);
    }
    paramsBody = paramsBody.join("&");

    let linkRedirect = cnrtCommonData.adminUrl+'admin.php?'+paramsBody;

    window.open(linkRedirect,"_self");
}

/**
 *
 */
class PricingTable extends React.Component {
    constructor(props) {
        super(props)
        this.state = {
            currencyName: 'usd',
            cycle:        'annual'
        }

        this.updateCurrency = this.updateCurrency.bind(this);
        this.updateCycle    = this.updateCycle.bind(this);
    }

    updateCurrency (event) {
        const target = event.target;
        const currencySelected = target.type === 'checkbox' ? target.checked : target.value;

        if (currencySelected === true) {
            this.setState({currencyName: 'eur'});
        } else {
            this.setState({currencyName: 'usd'});
        }
    }

    updateCycle (event) {
        this.setState({cycle: event.target.value});
    }

    render() {
        return (
            <>
                <div id="cnrt-radio-billing-cycle">
                    <input
                        type="radio"
                        id="cnrt-billing-cycle-monthly"
                        name="billing-cycle"
                        value="monthly"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "monthly"}
                    />
                    <label htmlFor='cnrt-billing-cycle-monthly'>
                        Monthly
                    </label>

                    <input
                        type="radio"
                        id="cnrt-billing-cycle-annual"
                        name="billing-cycle"
                        value="annual"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "annual"}
                    />
                    <label htmlFor='cnrt-billing-cycle-annual'>
                        Annual
                    </label>

                    <input
                        type="radio"
                        id="cnrt-billing-cycle-lifetime"
                        name="billing-cycle"
                        value="lifetime"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "lifetime"}
                    />
                    <label htmlFor='cnrt-billing-cycle-lifetime'>
                        Lifetime
                    </label>
                </div>

                <div id="cnrt-pricing-table">
                    <div className="cnrt-pricing-table-item">
                        <header className="cnrt-pricing-table-heading">
                            <h2 className="cnrt-pricing-table-title">Plus</h2>
                            <p className="cnrt-pricing-table-subtitle">5 websites</p>
                        </header>
                        <CnrtPricingPrintFeatures cycle={this.state.cycle} license='plus'/>
                        <div className="cnrt-pricing-table-footer">
                            <CnrtPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='plus'/>
                        </div>
                    </div>

                    <div className="cnrt-pricing-table-item is-highlighted">
                        <header className="cnrt-pricing-table-heading">
                            <h2 className="cnrt-pricing-table-title">Single</h2>
                            <p className="cnrt-pricing-table-subtitle"> 1 website</p>
                        </header>
                        <CnrtPricingPrintFeatures cycle={this.state.cycle} license='single'/>
                        <div className="cnrt-pricing-table-footer">
                            <CnrtPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='single'/>
                        </div>
                    </div>

                    <div className="cnrt-pricing-table-item">
                        <header className="cnrt-pricing-table-heading">
                            <h2 className="cnrt-pricing-table-title">Enterprise</h2>
                            <p className="cnrt-pricing-table-subtitle"> 30 websites</p>
                        </header>
                        <CnrtPricingPrintFeatures cycle={this.state.cycle} license='enterprise'/>
                        <div className="cnrt-pricing-table-footer">
                            <CnrtPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='enterprise'/>
                        </div>
                    </div>
                </div>

                <div id="switch-container">
                    <span className="cnrt-pricing-text-switcher"> Display Prices In US $ </span>
                    <label className="cnrt-pricing-switch">
                        <input type="checkbox" onChange={this.updateCurrency} />
                        <span className="cnrt-pricing-slider" />
                    </label>
                    <span className="cnrt-pricing-text-switcher"> €</span>
                </div>
            </>
        );
    }
}

ReactDOM.render(<PricingTable />, document.getElementById('cnrt-table-container'));

// Get the button that opens the modal
const btn   = document.getElementById('cnrt-link-policy');
const btn2  = document.getElementById('cnrt-link-policy-faq');
// Get the modal
const modal = document.getElementById('cnrt-refund-policy');
//
const close = document.getElementById('cnrt-close-modal-policy');

// When the user clicks on the button, open the modal
btn.addEventListener("click", ()=>{
    modal.style.display = "block";
    document.body.style.backgroundColor = 'rgba(0,0,0,0.7)'; /* Black w/ opacity */
});

// When the user clicks on the button, open the modal
btn2.addEventListener("click", ()=>{
    modal.style.display = "block";
    document.body.style.backgroundColor = 'rgba(0,0,0,0.7)'; /* Black w/ opacity */
});

// When the user clicks on <span> (x), close the modal
close.onclick = function() {
    modal.style.display = "none";
    document.body.style.backgroundColor = '#f1f1f1';
}