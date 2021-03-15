import logging
import os
import subprocess
import sys

import boto3
import botocore
from dotenv import load_dotenv
# from botocore.config import Config

env_path = os.path.join(os.path.dirname(os.path.realpath(__file__)), '.env')

load_dotenv(dotenv_path=env_path)

logging.basicConfig(stream=sys.stdout, level=logging.INFO)
logger = logging.getLogger('')

PROJECT_NAME = os.getenv('PROJECT_NAME')
VPC_NAME = PROJECT_NAME + "-vpc"
VPC_SUBBETS = os.getenv('VPC_SUBBETS')
RDS_NAME = PROJECT_NAME + "-rds"
DB_USERNAME = os.getenv('DB_USERNAME')
DB_PASSWORD = os.getenv('DB_PASSWORD')
DB_NAME = PROJECT_NAME
AWS_KEY = os.getenv('AWS_KEY')
AWS_SECRET = os.getenv('AWS_SECRET')
AWS_REGION = os.getenv('AWS_REGION', default='us-east-1')

# my_config = Config(
#     region_name = AWS_REGION,
# )

def create_vpc(session):
    ec2 = session.resource('ec2')

    # create VPC
    vpc = ec2.create_vpc(CidrBlock='172.24.0.0/16')

    # assign a name to our VPC
    vpc.create_tags(Tags=[{"Key": "Name", "Value": VPC_NAME}])

    vpc.wait_until_available()

    logger.info("created vpc {0}, id {1}, status {2}".format(VPC_NAME, vpc.id, vpc.state))

    # create an internet gateway and attach it to VPC
    igw = ec2.create_internet_gateway()
    vpc.attach_internet_gateway(InternetGatewayId=igw.id)

    logger.info("created internet gateway")

    # create a route table and a public route
    for routetable in vpc.route_tables.all():
        # there should be just one main route table
        route = routetable.create_route(DestinationCidrBlock='0.0.0.0/0', GatewayId=igw.id)
        logger.info("add internet gateway {0} to main route {1}".format(igw.id, routetable.id))
        # break

    # create subnet and associate it with route table
    subnet1 = ec2.create_subnet(CidrBlock='172.24.0.0/20', VpcId=vpc.id, AvailabilityZone='us-east-1a')
    routetable.associate_with_subnet(SubnetId=subnet1.id)

    subnet2 = ec2.create_subnet(CidrBlock='172.24.16.0/20', VpcId=vpc.id, AvailabilityZone='us-east-1b')
    routetable.associate_with_subnet(SubnetId=subnet2.id)

    subnet3 = ec2.create_subnet(CidrBlock='172.24.32.0/20', VpcId=vpc.id, AvailabilityZone='us-east-1c')
    routetable.associate_with_subnet(SubnetId=subnet3.id)

    subnet4 = ec2.create_subnet(CidrBlock='172.24.48.0/20', VpcId=vpc.id, AvailabilityZone='us-east-1d')
    routetable.associate_with_subnet(SubnetId=subnet4.id)

    logger.info("created subnet {0},{1},{2},{3}".format(subnet1.id, subnet2.id, subnet3.id, subnet4.id))

    logger.info("vpc created, id: {0}".format(vpc.id))

def create_aurora(session):
    
    instance_identifier=RDS_NAME # used for instance name and cluster name
    db_username=DB_USERNAME
    db_password=DB_PASSWORD
    db_port=3306
    # vpc_id,
    vpc_sg=[], # Must be an array
    # dbsubnetgroup_name,
    instance_type = "db.t2.small"
    extratags = []
    
    
    rds = session.client('rds')
    # Assume a DB SUBNET Groups exists before creating the cluster. 
    # You must have created a DBSUbnetGroup associated to the Subnet of the VPC of your cluster. AWS will find it automatically.

    # 
    # Search if the cluster exists
    try:

        db_cluster = rds.describe_db_clusters(
            DBClusterIdentifier = instance_identifier
        )['DBClusters']
        db_cluster = db_cluster[0]

        logger.info("found existing rds {0}".format(instance_identifier))

    except botocore.exceptions.ClientError   as e:

        logger.info("Creating empty cluster")


        # create subnet group
        subnet_group = rds.create_db_subnet_group(
            DBSubnetGroupName=PROJECT_NAME+'-rds-subnet-group',
            DBSubnetGroupDescription=PROJECT_NAME+'-rds-subnet-group',
            SubnetIds=VPC_SUBBETS.split(','),
        )

        res = rds.create_db_instance(
                    DBInstanceIdentifier = instance_identifier+'-instance-1',
                    DBInstanceClass='db.r5.large',
                    Engine="MySQL", # for MySQL 5.7-compatible Aurora
                    DBSubnetGroupName=PROJECT_NAME+'-rds-subnet-group',
                    AllocatedStorage=20,
                    EngineVersion='5.7.22',
                    MasterUsername=db_username,
                    MasterUserPassword=db_password,
                    )

#         res = rds.create_db_cluster(
#             AvailabilityZones=['us-east-1a'],
#             DBSubnetGroupName=PROJECT_NAME+'-rds-subnet-group',
#             DBClusterIdentifier = instance_identifier,
#             Engine="aurora-mysql", # for MySQL 5.7-compatible Aurora
#             EngineMode="provisioned",
#             MasterUsername=db_username,
#             MasterUserPassword=db_password,
# #             VpcSecurityGroupIds=['everforostandalone-EfnetNetwork-11VME2ICJ4K67',],
# #             VpcSubnetIds=['subnet-0e54063a23f75b19a',],
#             EnableHttpEndpoint=True,
#         )
#         db_cluster = res['DBCluster']
        db_cluster = res
#
#     cluster_name = db_cluster['DBClusterIdentifier']
#     instance_identifier = db_cluster['DBClusterIdentifier']
#
#     logger.info("Cluster identifier : %s, status : %s, members : %d\n", instance_identifier , db_cluster['Status'], len(db_cluster['DBClusterMembers']))
#
#     if (db_cluster['Status'] == 'deleting'):
#         logger.info(" Please wait for the cluster to be deleted and try again.\n")
#         return None
#
#     logger.info("   Writer Endpoint : %s\n", db_cluster['Endpoint'])
#     logger.info("   Reader Endpoint : %s\n", db_cluster['ReaderEndpoint'])

    # Now create instances
    # Loop on requested number of instance,
#     i=1
#     dbinstance_id = instance_identifier+"-"+str(i)
#     logger.info("Creating instance %d named '%s'", i, dbinstance_id)
    print(db_cluster)
    # try:
    #     res = rds.create_db_instance(
    #         DBInstanceIdentifier=dbinstance_id,
    #         DBInstanceClass=instance_type,
    #         # Engine='aurora-mysql',
    #         PubliclyAccessible=True,
    #         # AvailabilityZone=None,
    #         # DBSubnetGroupName=dbsubnetgroup_name,
    #         DBClusterIdentifier=instance_identifier,
    #         # Tags = psa.tagsKeyValueToAWStags(extratags)
    #     )['DBInstance']

    #     logger.info(" DbiResourceId=%s\n", res['DbiResourceId'])

    # except botocore.exceptions.ClientError   as e:
    #     logger.info(" Instance seems to exists.\n")
    #     res = rds.describe_db_instances(DBInstanceIdentifier = dbinstance_id)['DBInstances']
    #     logger.info(" Status is %s\n", res[0]['DBInstanceStatus'])

    return db_cluster


if __name__ == "__main__":

    if len(sys.argv) == 1:
        raise Exception("pass one of the argument 'vpc', 'ecs', 'rds', 'data'")

    session = boto3.Session(
        aws_access_key_id=AWS_KEY,
        aws_secret_access_key=AWS_SECRET,
        region_name=AWS_REGION
    )

    if sys.argv[1] == 'vpc':
        create_vpc(session)

    if sys.argv[1] == 'rds':
        create_aurora(session)

    if sys.argv[1] == 'ecs':
        subprocess.run(["docker", "compose", "up", "--project-name", "efenterprise"])

    if sys.argv[1] == 'ps':
        subprocess.run(["docker", "compose", "ps", "--project-name", "efenterprise"])

    if sys.argv[1] == 'down':
        subprocess.run(["docker", "compose", "down", "--project-name", "efenterprise"])

    # ec2 = session.resource('ec2')

    # vpc = ec2.Vpc('vpc-0225e92e93762c532')

    # for route_table in vpc.route_tables.all():
    #     print(route_table.routes_attribute)