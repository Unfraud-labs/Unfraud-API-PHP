<?php

namespace Unfraud\Model;

/**
 * Class Score
 * @package Unfraud\Model
 *
 * @property integer $creditsRemaining The approximate number of service
 * credits remaining on your account.
 * @property string $id This is a UUID that identifies the minFraud request.
 * Please use this ID in bug reports or support requests to Unfraud so that we
 * can easily identify a particular request.
 * @property float $riskScore This property contains the risk score, from 0.01
 * to 99. A higher score indicates a higher risk of fraud. For example, a
 * score of 20 indicates a 20% chance that a transaction is fraudulent. We
 * never return a risk score of 0, since all transactions have the possibility
 * of being fraudulent. Likewise we never return a risk score of 100.
 * @property array $warnings This array contains
 * {@link \Unfraud\Unfraud\Model\Warning Warning} objects detailing issues
 * with the request that was sent, such as invalid or unknown inputs. It
 * is highly recommended that you check this array for issues when integrating
 * the web service.
 */
class Score extends AbstractModel
{

    /**
     * @internal
     */
    protected $timestamp;

    /**
     * @internal
     */
    protected $label;

    /**
     * @internal
     */
    protected $highlights;

    /**
     * @internal
     */
    protected $success;

    /**
     * @internal
     */
    protected $unfraudScore;


    public function __construct($response)
    {
        print_r($response);exit;
        $this->timestamp = $this->safeArrayLookup($response['timestamp']);
        $this->label = $this->safeArrayLookup($response['unfraud_label']);
        $this->highlights = $this->safeArrayLookup($response['unfraud_highlights']);
        $this->success = $this->safeArrayLookup($response['success']);
        $this->unfraudScore = $this->safeArrayLookup($response['unfraud_score']);

    }
}
