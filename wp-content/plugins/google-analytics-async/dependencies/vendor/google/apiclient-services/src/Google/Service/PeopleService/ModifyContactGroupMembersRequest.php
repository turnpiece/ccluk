<?php

namespace Beehive;

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
class Google_Service_PeopleService_ModifyContactGroupMembersRequest extends \Beehive\Google_Collection
{
    protected $collection_key = 'resourceNamesToRemove';
    public $resourceNamesToAdd;
    public $resourceNamesToRemove;
    public function setResourceNamesToAdd($resourceNamesToAdd)
    {
        $this->resourceNamesToAdd = $resourceNamesToAdd;
    }
    public function getResourceNamesToAdd()
    {
        return $this->resourceNamesToAdd;
    }
    public function setResourceNamesToRemove($resourceNamesToRemove)
    {
        $this->resourceNamesToRemove = $resourceNamesToRemove;
    }
    public function getResourceNamesToRemove()
    {
        return $this->resourceNamesToRemove;
    }
}
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
\class_alias('Beehive\\Google_Service_PeopleService_ModifyContactGroupMembersRequest', 'Google_Service_PeopleService_ModifyContactGroupMembersRequest', \false);