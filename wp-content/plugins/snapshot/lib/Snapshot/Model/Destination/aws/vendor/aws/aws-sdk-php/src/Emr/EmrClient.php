<?php
namespace Aws\Emr;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic MapReduce (Amazon EMR)** service.
 *
 * @method \Aws\Result addInstanceFleet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addInstanceFleetAsync(array $args = [])
 * @method \Aws\Result addInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addInstanceGroupsAsync(array $args = [])
 * @method \Aws\Result addJobFlowSteps(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addJobFlowStepsAsync(array $args = [])
 * @method \Aws\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \Aws\Result cancelSteps(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelStepsAsync(array $args = [])
 * @method \Aws\Result createSecurityConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSecurityConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteSecurityConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSecurityConfigurationAsync(array $args = [])
 * @method \Aws\Result describeCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeClusterAsync(array $args = [])
 * @method \Aws\Result describeJobFlows(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeJobFlowsAsync(array $args = [])
 * @method \Aws\Result describeSecurityConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSecurityConfigurationAsync(array $args = [])
 * @method \Aws\Result describeStep(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStepAsync(array $args = [])
 * @method \Aws\Result getBlockPublicAccessConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBlockPublicAccessConfigurationAsync(array $args = [])
 * @method \Aws\Result listBootstrapActions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBootstrapActionsAsync(array $args = [])
 * @method \Aws\Result listClusters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \Aws\Result listInstanceFleets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstanceFleetsAsync(array $args = [])
 * @method \Aws\Result listInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstanceGroupsAsync(array $args = [])
 * @method \Aws\Result listInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstancesAsync(array $args = [])
 * @method \Aws\Result listSecurityConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSecurityConfigurationsAsync(array $args = [])
 * @method \Aws\Result listSteps(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStepsAsync(array $args = [])
 * @method \Aws\Result modifyCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyClusterAsync(array $args = [])
 * @method \Aws\Result modifyInstanceFleet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyInstanceFleetAsync(array $args = [])
 * @method \Aws\Result modifyInstanceGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyInstanceGroupsAsync(array $args = [])
 * @method \Aws\Result putAutoScalingPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putAutoScalingPolicyAsync(array $args = [])
 * @method \Aws\Result putBlockPublicAccessConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBlockPublicAccessConfigurationAsync(array $args = [])
 * @method \Aws\Result removeAutoScalingPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeAutoScalingPolicyAsync(array $args = [])
 * @method \Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \Aws\Result runJobFlow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise runJobFlowAsync(array $args = [])
 * @method \Aws\Result setTerminationProtection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setTerminationProtectionAsync(array $args = [])
 * @method \Aws\Result setVisibleToAllUsers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise setVisibleToAllUsersAsync(array $args = [])
 * @method \Aws\Result terminateJobFlows(array $args = [])
 * @method \GuzzleHttp\Promise\Promise terminateJobFlowsAsync(array $args = [])
 */
class EmrClient extends AwsClient {}