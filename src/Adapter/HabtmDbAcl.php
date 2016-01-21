<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Acl\Adapter;

use Acl\AclInterface;
use Cake\Controller\Component;
use Cake\Core\App;
use Cake\ORM\TableRegistry;

/**
 * DbAcl implements an ACL control system in the database. ARO's and ACO's are
 * structured into trees and a linking table is used to define permissions. You
 * can install the schema for DbAcl with the Schema Shell.
 *
 * `$aco` and `$aro` parameters can be slash delimited paths to tree nodes.
 *
 * eg. `controllers/Users/edit`
 *
 * Would point to a tree structure like
 *
 * {{{
 *    controllers
 *        Users
 *            edit
 * }}}
 *
 */
class HabtmDbAcl extends DbAcl
{
	/**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

	/**
	* Checks if the given $aro has access to action $action in $aco
	* Check returns true once permissions are found, in following order:
	* User node
	* User::parentNode() node
	* Groupnodes of Groups that User has habtm links to
	*
	* @param string $aro ARO The requesting object identifier.
	* @param string $aco ACO The controlled object identifier.
	* @param string $action Action (defaults to *)
	* @return boolean Success (true if ARO has access to action in ACO, false otherwise)
	*/
	public function check($aro, $aco, $action = "*")
	{
		if (parent::check($aro, $aco, $action)) {
			return true;
		}
		$habtmTable = TableRegistry::get('GroupsUsers');
		if ( !$habtmTable ){
			return false;
		}
		$userGroups = $habtmTable->find('all')->select(['group_id'])->where(['GroupsUsers.user_id' => $aro['Users']['id']])->all()->toArray();
		foreach ($userGroups as $group) {
			$aro = ['Groups' => ['id' => $group->group_id]];
			if ( $this->Permission->check($aro, $aco, $action) ){
				return true;
			}
		}
		return false;
	}
}
