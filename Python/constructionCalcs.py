# make any imports you may need here
import math
# include copies of each of the functions that you created
# for the four previous parts of this challenge here

def number_of_stoplights(miles, lanes):
    stoplights_per_intersection = 2 + lanes
    number_of_intersections = int(miles)
    total_stoplights = stoplights_per_intersection * number_of_intersections
    return total_stoplights

def truckloads_of_asphalt(miles, lanes, depth_inches):
    road_length = miles * 5280
    road_width = lanes * 12
    road_depth = depth_inches / 12
    asphalt_cubic_feet = road_length * road_width * road_depth
    asphalt_pounds = asphalt_cubic_feet * 145
    approximate_truck_loads = asphalt_pounds / 10000
    total_truckloads = math.ceil(approximate_truck_loads)
    return total_truckloads

def number_of_power_pipes(miles):
    road_length = miles * 5280
    approximate_power_pipes = road_length / 20
    total_power_pipes = math.ceil(approximate_power_pipes)
    return total_power_pipes

def number_of_water_pipes(miles):
    road_length = miles * 5280
    approximate_water_pipes = road_length / 10
    total_water_pipes = math.ceil(approximate_water_pipes)
    return total_water_pipes

def crew_members(miles, lanes, days):
    crew = (50 * miles * lanes) / days
    return math.ceil(crew)

# use the template below to write a complete and functioning program

# =========================================================
# Collect the user inputs. Remember that Python's input
# function always returns a string, so you may need to cast
# or convert the user inputs to float or int.

# --Prompt user to enter length of road project (in miles)
# --Be sure to store the input in a variable
miles = float(input("Enter Road Project Length in Miles : "))

# --Prompt user to enter number of lanes
# --Be sure to store the input in a variable
lanes =   int(input("Enter Number of Lanes              : "))

# --Prompt user to enter depth of asphalt (in inches)
# --Be sure to store the input in a variable
depth =   int(input("Enter Depth of Asphalt in Inches   : "))

# --Prompt user to enter days to complete project
# --Be sure to store the input in a variable
days  =   int(input("Enter Days to Complete Project     : "))

#----------------------------------------------------------

# =========================================================
# Use the functions you created to calculate and store the
# amount of materials and resources needed for the project.
# you will pass the values that you collected from the user
# to these functions as they are needed.

# --call function to get truck loads of asphalt and store return value in a variable named asphalt_truckloads
asphalt_truckloads = truckloads_of_asphalt(miles, lanes, depth)

# --call function to get number of stoplights and store return value in a variable named stoplights
stoplights = number_of_stoplights(miles, lanes) 

# --call function to get number of water pipes and store return value in a variable named water_pipes
water_pipes = number_of_water_pipes(miles)

# --call function to get number of power pipes and store return value in a variable named power_pipes
power_pipes = number_of_power_pipes(miles)

# --call function to get number of crew members and store return value in a variable named number_of_crew_members
number_of_crew_members = crew_members(miles, lanes, days)

# Use the values you calculate and stored above to compute
# the cost of each item

# --calculate cost of asphalt and store in a variable named cost_of_asphalt
cost_of_asphalt = asphalt_truckloads * 750

# --calculate cost of stoplights and store in a variable named cost_of_stoplights
cost_of_stoplights = stoplights * 25000

# --calculate cost of water pipes and store in a variable named cost_of_water_pipes
cost_of_water_pipes = water_pipes * 200

# --calculate cost of power pipes and store in a variable named cost_of_power_pipes
cost_of_power_pipes = power_pipes * 400

# --calculate cost of labor (crew members) and store in a variable named cost_of_labor
cost_of_labor = number_of_crew_members * days * 20 * 8

# --calculate total cost of all materials plus labor and store in a variable named total_cost
total_cost = cost_of_asphalt + cost_of_stoplights + cost_of_water_pipes + cost_of_power_pipes + cost_of_labor

#----------------------------------------------------------

# =========================================================
# display the results for the user.

print("=== Amount of materials needed ===")
print("Truckloads of Asphalt :", asphalt_truckloads)
print("Stoplights            :", stoplights)
print("Water pipes           :", water_pipes)
print("Power pipes           :", power_pipes)
print("Crew members needed   :", number_of_crew_members)
print("=== Cost of Materials ============")
print("Cost of Asphalt       :", cost_of_asphalt)
print("Cost of Stoplights    :", cost_of_stoplights)
print("Cost of Water pipes   :", cost_of_water_pipes)
print("Cost of Power pipes   :", cost_of_power_pipes)
print("Cost of Labor         :", cost_of_labor)
print("=== Total Cost of Project ========")
print("Total cost of project :", total_cost)
#----------------------------------------------------------