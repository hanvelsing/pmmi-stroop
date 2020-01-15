# Example Analysis 

# Case: Two simple conditions that are explicitely saved as a column
#       Incorrect reactions are coded as -999 in reaction times

# 1. Aggregation of singular participant files

# Getting a list of all files with ending csv in current directory
all_files <- list.files(
  path = ".",
  pattern = "*.csv"
)

# The final data frame in which we aggregate all data
x_complete <- data.frame(
  id = c(),
  valid_cases = c(),
  rt_cond1 = c(),
  rt_cond2 = c()
)


# Two conditions: 0 and 1

for (f in all_files) {
  
  # getting file contents
  x_temp <- read.table(
    file = f,
    header = T,
    sep = ",",
    dec = ".",
    quote='"',
    stringsAsFactors = FALSE
    )
  
  # remove invalid cases with rt = -999
  x_temp <- x_temp[x_temp$v_correct != -999,]
  x_temp <- x_temp[x_temp$v_correct != 0,]
  x_temp <- x_temp[x_temp$test_part != "test",]
  
  # cound valid cases
  valid_cases_temp <- nrow(x_temp)
 
  # cast to numeric 
  x_temp$rt <- as.numeric(x_temp$rt)
  
  # mean reaction time for condition 0
  rt_cond1_temp <- mean(
    x_temp$rt[x_temp$v_cond == 1]
  )
  
  # mean reaction time for condition 1
  rt_cond2_temp <- mean(
    x_temp$rt[x_temp$v_cond == 2]
  )
  
  # participant id
  id_temp <- x_temp$id[1]
  
  
  # add a row with all relevant data to the complete data frame
  x_complete <- rbind(
    x_complete, 
    data.frame(
      id  = id_temp,
      valid_cases = valid_cases_temp,
      rt_cond1 = rt_cond1_temp,
      rt_cond2 = rt_cond2_temp
    )
  )
  
}

# 2. Check if everything has been saved correctly

print(x_complete)


# 4. Do a simple description

library(psych)


print(describe(x_complete))

# 5. Test your hypothesis

result <- t.test(
  x_complete$rt_cond1,
  x_complete$rt_cond2
)

print(result)
