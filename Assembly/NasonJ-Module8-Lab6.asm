# Write a program that uses two loops to find the integer values that are in both arrays.  
# When an integer value is found to be in both arrays, place the integer value in a $t register.  
# The $t registers should have the integer values placed in them in this way:
# - the first integer value found to be in both arrays should be placed in the register $t0
# - the second integer value found to be in both arrays should be placed in the register $t1

.data # data segment     
array1:     .word 29, 106, 18, 2, 55, 21, 17, 13, 9999, 1024, 13, 2, 5, 23, 51, 2021, 111, 89, 89, 91, 861, 1234, 5004
array2:     .word 91, 15, 767, 861, 89, 21, 1000, 1234
outerLength:    .word 23
innerLength:    .word 8
sum:	        .word 0 # declare sum as zero

.text # code segment

main:
    lw $s7, outerLength # load length into $s7
    addi $s2, $zero, 0 # initialize $s2 to zero to use as counter
    la $a0, array1 # load address of array1 into $a0

# Variable list
# $s0 = outerLength
# $s1 = innerLength
# $s2 = outer counter
# $s3 = inner counter
# $a0 = pointer address of array1
# $a1 = pointer address of array2
# $t7 = value at address $a0 to compare
# $t0 = first matching array value
# $t1 = second matching array value
# $t3 = third matching array value
# $t4 = fourth matching array value

outerLoop:
    beq $s1, $s7, end # if the counter is equal to the length of the outerArray, end
    lw $s1, innerLength # load length into $s6
    la $a1, array2 # load address of array2 into $a1

    addi $s3, $zero, 0 # initialize $s3 to zero to use as counter for inner loop

    innerLoop:
        beq $s3, $s1, end # if the counter is equal to the length of the innerArray, end
        lw $t7, 0($a0) # load value at address $a0 into $t7 outer array value
        addi $a0, $a0, 4 # increment address by 4 for outer array
        lw $t8, 0($a1) # load value at address $a1 into $t8 inner array value

        addi $s3, $s3, 1 # increment $s3 by 1 for inner counter

        bne $t7, $t8, innerLoop # if $t7 is NOT equal to $t1, jump to innerLoop again 

        #since they matched, just store the value in the correct register and jump to outerLoop
        
        j outerLoop # jump to outerLoop

    lw $t2, 0($a0) # load value at address $a0 into $t2
    add $t3, $t2, $t3 # add $t2 to $t3 and store in $t3
    addi $a0, $a0, 4 # increment address by 4
    addi $t1, $t1, 1 # increment $t1 by 1
    j outerLoop # jump to loop

end:

# just for testing
#    li $v0, 1 # load 1 into $v0
#    la $a0, sum # load address of sum into $a0
#    syscall # print sum

#clean exit
    li $v0, 10 # load 10 into $v0
    syscall # exit
