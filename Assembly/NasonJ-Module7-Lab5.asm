# Write a program which uses an assembly language loop to add all the array element values, and examine how the sum of all the array element values is compares to the value 767.  
# Do this by using the loop to add each array element value into a register you choose to hold the sum, and then check to how the sum in that register compares to the value 767.  

# if (sum < 767):
#	$t0 = 0
# elseif (sum == 767):
#	$t0 = 1
# else
#	$t0 = 2

.data # data segment            
tenints:  .word 3, 17, 12, 9, 36, 102, 81, 500, -1, 9
length: .word 10 # declare and initialize a byte variable
sum:	.word 0 # declare sum as zero

.text # code segment

main:
    lw $t0, length # load length into $t0
    la $a0, tenints # load address of tenints into $a0
    add $t1, $zero, $zero # initialize $t1 to zero

loop:
    beq $t1, $t0, end # if $t1 == $t0, end
    lw $t2, 0($a0) # load value at address $a0 into $t2
    add $t3, $t2, $t3 # add $t2 to $t3 and store in $t3
    addi $a0, $a0, 4 # increment address by 4
    addi $t1, $t1, 1 # increment $t1 by 1
    j loop # jump to loop

end:

# just for testing
    li $v0, 1 # load 1 into $v0
    la $a0, sum # load address of sum into $a0
    syscall # print sum

#clean exit
    li $v0, 10 # load 10 into $v0
    syscall # exit
